<?php

namespace Modules\BillingBase\Entities;

use \Modules\BillingBase\Console\SettlementRunCommand;

class SettlementRun extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'settlementrun';

    // don't try to add these Input fields to Database of this model
    public $guarded = ['rerun', 'sepaaccount', 'fullrun', 'banking_file_upload'];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            // see SettlementRunController@prepare_rules
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

    public function parseBankingFile($mt940)
    {
        $parser = new \Kingsquare\Parser\Banking\Mt940();
        $transactionParser = new \Modules\Dunning\Entities\TransactionParser;

        $statements = $parser->parse($mt940);
        foreach ($statements as $statement) {
            foreach ($statement->getTransactions() as $transaction) {
                $debt = $transactionParser->parse($transaction);

                // only for analysis during development!
                // $transactions[$transaction->getDebitCredit()][] = ['price' => $transaction->getPrice(), 'description' => explode('?', $transaction->getDescription())];

                if (! $debt) {
                    continue;
                }

                $debt->save();
            }
        }
        // d($transactions, $statements, str_replace(':61:', "\r\n---------------\r\n:61:", $mt940));
    }
}

class SettlementRunObserver
{
    public function creating($settlementrun)
    {
        // dont show every settlementrun that was created in one month
        $time = strtotime('first day of last month');
        SettlementRun::where('month', '=', date('m', $time))->where('year', '=', date('Y', $time))->delete();
        $settlementrun->fullrun = 1;
    }

    public function created($settlementrun)
    {
        if (! $settlementrun->observer_enabled) {
            return;
        }

        // NOTE: Make sure that we use Database Queue Driver - See .env!
        // \Artisan::call('billing:accounting', ['--debug' => 1]);
        \Session::put('job_id', \Queue::push(new SettlementRunCommand($settlementrun)));
    }

    public function updated($settlementrun)
    {
        if (\Input::has('rerun')) {
            // Make sure that settlement run is queued only once
            $queued = \DB::table('jobs')->where('payload', 'like', '%SettlementRunCommand%')->count();
            if (! $queued) {
                $acc = \Input::get('sepaaccount') ? SepaAccount::find(\Input::get('sepaaccount')) : null;
                \Session::put('job_id', \Queue::push(new SettlementRunCommand($settlementrun, $acc)));
            }
        }

        // TODO: implement this as command and queue this?
        if (\Input::hasFile('banking_file_upload')) {
            SettlementRun::where('id', $id)->update(['uploaded_at' => date('Y-m-d H:i:s')]);

            $mt940 = \Input::file('banking_file_upload');

            foreach ($mt940s as $mt940) {
                $settlementrun->parseBankingFile($mt940);
            }

        }
    }
}
