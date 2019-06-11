<?php

namespace Modules\Ccc\Http\Controllers;

use App\Http\Controllers\BaseViewController;

class CccController extends \BaseController
{
    /**
     * defines the formular fields for the edit view
     */
    public function view_form_fields($model = null)
    {
        $languageDirectories = BaseViewController::getAllLanguages();
        $languages = BaseViewController::generateLanguageArray($languageDirectories);

        // label has to be the same like column in sql table
        $a = [
            ['form_type' => 'text', 'name' => 'headline1', 'description' => 'Headline 1'],
            ['form_type' => 'text', 'name' => 'headline2', 'description' => 'Headline 2'],
            ['form_type' => 'select', 'name' => 'language', 'description' => 'Language',
                'value' => $languages,
                'help' => trans('helper.translate').' https://crowdin.com/project/nmsprime', ],
        ];

        $b = [];
        if (! \Module::collections()->has('BillingBase')) {
            // See CompanyController in Case BillingBase is active
            $files = self::get_storage_file_list('ccc/template/');

            $b = [
                ['form_type' => 'select', 'name' => 'template_filename', 'description' => 'Connection Info Template', 'value' => $files, 'help' => 'Tex Template used to Create Connection Information on the Contract Page for a Customer'],
                ['form_type' => 'file', 'name' => 'template_filename_upload', 'description' => 'Upload Template'],
                ];
        }

        return array_merge($a, $b);
    }

    /**
     * Overwrites the base methods to handle file uploads
     */
    public function store($redirect = true)
    {
        $dir = storage_path('app/config/ccc/template/');
        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        // check and handle uploaded firmware files
        $this->handle_file_upload('template_filename', $dir);

        // finally: call base method
        return parent::store();
    }

    public function update($id)
    {
        $dir = storage_path('app/config/ccc/template/');
        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $this->handle_file_upload('template_filename', storage_path('app/config/ccc/template/'));

        return parent::update($id);
    }
}
