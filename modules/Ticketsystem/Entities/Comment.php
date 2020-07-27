<?php

namespace Modules\Ticketsystem\Entities;

use App\User;

class Comment extends \BaseModel
{
    protected $table = 'comment';

    public static function view_headline()
    {
        return 'Comments';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-commenting-o"></i>';
    }

    public function view_index_label()
    {
        return [
            'header' => $this->id.' - '.substr_replace($this->comment, '...', 25),
        ];
    }

    public function view_belongs_to()
    {
        return $this->ticket;
    }

    /**
     * Relation views
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Relation user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
