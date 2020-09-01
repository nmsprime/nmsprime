<?php

namespace Modules\Ticketsystem\Entities;

class TicketType extends \BaseModel
{
    public $table = 'ticket_type';

    public function rules()
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
        return $this->belongsToMany(Ticket::class, 'ticket_type_ticket', 'ticket_id', 'ticket_type_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class);
    }
}
