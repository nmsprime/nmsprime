<?php

namespace Modules\BillingBase\Entities;

class SettlementRun extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'settlementrun';

    // don't try to add these Input fields to Database of this model
    public $guarded = ['rerun', 'sepaaccount'];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            // 'month' => 'unique:settlementrun,month,'.$id.',id,year,'.$year.',deleted_at,NULL', //,year,'.$year
        ];
    }

    /**
     * Init Observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new SettlementRunObserver);
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Settlement Run';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-file-pdf-o"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();
        $day = (isset($this->created_at)) ? $this->created_at : '';

        return ['table' 		=> $this->table,
                'index_header' 	=> [$this->table.'.year',
                                    $this->table.'.month',
                                    $this->table.'.created_at',
                                    'verified', ],
                'header' 		=>  $this->year.' - '.$this->month.' - '.$day,
                'bsclass' 		=> $bsclass,
                'order_by' 		=> ['0' => 'desc'],
                'edit' 			=> ['verified' => 'run_verified',
                                    'created_at' => 'created_at_toDateString', ],
                ];
    }

    public function get_bsclass()
    {
        return $this->verified ? 'info' : 'warning';
    }

    public function run_verified()
    {
        return  $this->verified ? 'Yes' : 'No';
    }

    public function set_index_delete()
    {
        if ($this->verified) {
            $this->index_delete_disabled = true;
        }

        return $this->index_delete_disabled;
    }

    public function created_at_toDateString()
    {
        return $this->created_at->toDateString();
    }

    public function view_has_many()
    {
        $ret['Edit']['Files']['view']['view'] = 'billingbase::SettlementRun.files';
        $ret['Edit']['Files']['view']['vars']['files'] = $this->accounting_files();

        // option to rerun settlementrun only for a specific SepaAccount
        if (SepaAccount::count() > 1) {
            $accs1 = [0 => trans('messages.ALL')];
            $accs2 = $this->html_list(SepaAccount::orderBy('id')->get(), ['id', 'name'], false, ': ');
            $accs = $accs1 + $accs2;
            $ret['Edit']['Files']['view']['vars']['sepaaccs'] = $accs;
        }

        // NOTE: logs are fetched in SettlementRunController::edit
        $ret['Edit']['Logs']['view']['view'] = 'billingbase::SettlementRun.logs';
        $ret['Edit']['Logs']['view']['vars']['md_size'] = 12;

        return $ret;
    }

    /**
     * Mutator function to get accounting storage directory path via: model->directory
     * (so calling it in e.g. constructor is superfluous, used in e.g. ZipCommand)
     *
     * @return string   SettlementRun absolute directory path
     */
    public function getDirectoryAttribute()
    {
        return storage_path('app/'.$this->getRelativeDirectoryAttribute());
    }

    /**
     * Mutator function to get accounting storage directory path via: model->relativeDirectory
     *
     * @return string   SettlementRun relative directory path
     */
    public function getRelativeDirectoryAttribute()
    {
        return 'data/billingbase/accounting/'.$this->year.'-'.str_pad($this->month, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Relations
     */
    public function invoices()
    {
        return $this->hasMany('Modules\BillingBase\Entities\Invoice');
    }

    /**
     * Return all Billing Files the corresponding directory contains
     *
     * @return array 	containing all files ordered for view
     */
    public function accounting_files()
    {
        if (! is_dir($this->directory)) {
            return [];
        }

        $arr = [];

        $files = \File::allFiles($this->directory);

        //order files
        foreach ($files as $file) {
            $sepaacc = $file->getRelativePath() ?: \App\Http\Controllers\BaseViewController::translate_label('General');
            $arr[$sepaacc][] = $file;
        }

        return $arr;
    }
}

class SettlementRunObserver
{
    public function creating($settlementrun)
    {
        // dont show every settlementrun that was created in one month
        $time = strtotime('first day of last month');
        SettlementRun::where('month', '=', date('m', $time))->where('year', '=', date('Y', $time))->delete();
    }

    public function created($settlementrun)
    {
        if (! $settlementrun->observer_enabled) {
            return;
        }

        // NOTE: Make sure that we use Database Queue Driver - See .env!
        $job_id = \Queue::push(new \Modules\BillingBase\Console\SettlementRunCommand($settlementrun));
        // \Artisan::call('billing:accounting', ['--debug' => 1]);
        \Session::put('job_id', $job_id);
    }

    public function updated($settlementrun)
    {
        if (\Input::has('rerun')) {
            $acc = \Input::get('sepaaccount') ? SepaAccount::find(\Input::get('sepaaccount')) : null;
            \Session::put('job_id', \Queue::push(new \Modules\BillingBase\Console\SettlementRunCommand($settlementrun, $acc)));
        }
    }

    public function deleted($settlementrun)
    {
        // delete all invoices & accounting record files - maybe use SettlementRunCommand@_directory_cleanup
        $date = $settlementrun->year.'-'.str_pad($settlementrun->month, 2, '0', STR_PAD_LEFT);
        $dir = 'data/billingbase/accounting/'.$date;

        \Modules\BillingBase\Http\Controllers\SettlementRunController::directory_cleanup($settlementrun);
    }
}
