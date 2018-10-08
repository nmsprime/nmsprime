<?php

namespace Modules\Ticketsystem\Http\Controllers;

use Modules\Ticketsystem\Entities\TicketType;

class TicketTypeController extends \BaseController
{
    protected $index_tree_view = true;

    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new TicketType;
        }

        $parents = $model->html_list(TicketType::where('id', '!=', $model->id)->get()->all(), 'name', true);

        return [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'select', 'name' => 'parent_id', 'description' => 'Parent', 'value' => $parents],
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];
    }
}
