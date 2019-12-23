<?php

namespace Modules\BillingBase\Http\Controllers;

use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\Salesman;

class SalesmanController extends \BaseController
{
    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Salesman;
        }

        $types = implode(', ', Product::getPossibleEnumValues('type'));

        // label has to be the same like column in sql table
        return [
            ['form_type' => 'text', 'name' => 'firstname', 'description' => 'Firstname'],
            ['form_type' => 'text', 'name' => 'lastname', 'description' => 'Lastname'],
            ['form_type' => 'text', 'name' => 'commission', 'description' => 'Commission in %'],
            ['form_type' => 'text', 'name' => 'products', 'description' => 'Product Types', 'help' => trans('helper.Salesman_ProductList').$types, 'options' => ['placeholder' => $types]],
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];
    }

    public function prepare_input($data)
    {
        // $data['products'] = str_replace(['/', '|', ';'], ',', trim(ucwords(strtolower($data['products']))));
        $data['products'] = str_replace(['/', '|', ';'], ',', \Str::title($data['products']));
        $data['products'] = str_replace('Tv', 'TV', $data['products']);

        return parent::prepare_input($data);
    }
}
