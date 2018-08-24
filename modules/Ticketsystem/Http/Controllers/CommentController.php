<?php

namespace Modules\Ticketsystem\Http\Controllers;

use Modules\Ticketsystem\Entities\Comment;

class CommentController extends \BaseController
{
    protected $index_delete_allowed = false;

    public function view_form_fields($model = null)
    {
        return [
            ['form_type' => 'textarea', 'name' => 'comment', 'description' => 'Comment'],
            ['form_type' => 'text', 'name' => 'ticket_id', 'hidden' => 1],
            ['form_type' => 'text', 'name' => 'user_id', 'hidden' => 1, 'value' => \Auth::user()->id],
        ];
    }

    /*
     * Prepared Redirect Back function because in this case the user will likely not want to see the edit view again
     * This should be done by extending the generic store function by more options for the redirect param
     */
    // public function store($redirect = true)
    // {
    // 	$id = parent::store(false);
    // 	$comment = Comment::find($id);

    // 	return \Redirect::route('Ticket.edit', $comment->ticket_id);
    // }
}
