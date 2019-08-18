<?php

namespace Modules\Ticketsystem\Entities;

class TicketType extends \BaseModel
{
    public $table = 'tickettype';

    public static function rules($id = null)
    {
        return [
            'name' => 'required|string',
        ];
    }

    /**
     * View Stuff
     */
    public static function view_headline()
    {
        return 'TicketTypes';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-ticket"></i>';
    }

    public function view_index_label()
    {
        return $this->name;
    }

    /**
     * Relations
     */
    public function tickets()
    {
        return $this->belongsToMany(Ticket::class, 'tickettype_ticket', 'ticket_id', 'tickettype_id');
    }

    public function children()
    {
        return $this->hasMany('\Modules\Ticketsystem\Entities\TicketType', 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo('\Modules\Ticketsystem\Entities\TicketType');
    }
}
