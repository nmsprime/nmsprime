<?php

namespace Modules\BillingBase\Entities;

class Company extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'company';

    // All HTML Input Fields that are discarded during Database Update
    public $guarded = ['logo_upload', 'conn_info_template_fn_upload'];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            // 'name' => 'required|unique:cmts,hostname,'.$id.',id,deleted_at,NULL'  	// unique: table, column, exception , (where clause)
            'name' 		=> 'required',
            'street' 	=> 'required',
            'zip'	 	=> 'required',
            'city'	 	=> 'required',
            'logo_upload' => 'mimes:jpg,jpeg,bmp,png,pdf',
            'conn_info_template_fn_upload' => 'mimetypes:text/x-tex,application/x-tex',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Company';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-industry"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();

        return ['table' => $this->table,
                'index_header' => [$this->table.'.name', $this->table.'.city', $this->table.'.phone', $this->table.'.mail'],
                'bsclass' => $bsclass,
                'header' => $this->name,
                'order_by' => ['0' => 'asc'], // columnindex => direction
                ];
    }

    public function get_bsclass()
    {
        $bsclass = 'info';

        return $bsclass;
    }

    public function view_has_many()
    {
        $ret['Edit']['SepaAccount']['class'] = 'SepaAccount';
        $ret['Edit']['SepaAccount']['relation'] = $this->accounts;

        return $ret;
    }

    /**
     * Relationships:
     */
    public function accounts()
    {
        return $this->hasMany('Modules\BillingBase\Entities\SepaAccount');
    }

    /*
     * Init Observers
     */
    public static function boot()
    {
        self::observe(new CompanyObserver);
        parent::boot();
    }

    /**
     * Prepare data array with keys replaced by values in tex templates for pdf creation
     *
     * @return array
     *
     * @author Nino Ryschawy
     */
    public function template_data()
    {
        $class = 'company';
        $ignore = ['created_at', 'updated_at', 'deleted_at', 'id'];
        $data = [];

        foreach ($this->attributes as $key => $value) {
            if (in_array($key, $ignore)) {
                continue;
            }

            // separate comma separated values by linebreakings
            if (in_array($key, ['management', 'directorate'])) {
                $value = explode(',', $value);
                $tmp = [];

                foreach ($value as $name) {
                    $tmp[] = trim(escape_latex_special_chars($name));
                }

                $data[$class.'_'.$key] = implode('\\\\', $tmp);

                continue;
            } elseif (! in_array($key, ['zip', 'conn_info_template_fn', 'logo'])) {
                $value = escape_latex_special_chars($value);
            }

            $data[$class.'_'.$key] = $value;
        }

        return $data;
    }
}

class CompanyObserver
{
    public function updated($company)
    {
        \Artisan::call('queue:restart');
    }
}
