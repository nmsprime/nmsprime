<?php

namespace Modules\Ticketsystem\Entities;

use Modules\Ticketsystem\Entities\Assignee;

class Ticket extends \BaseModel {

    public $table = 'ticket';

	public static function boot()
	{
		parent::boot();
		Ticket::observe(new TicketObserver);
	}

	public static function view_headline()
	{
		return 'Tickets';
	}

	public static function view_icon()
	{
		return '<i class="fa fa-ticket"></i>';
	}

	public function index_list()
	{
		return $this->orderBy('id', 'desc')->get();
	}

	public function view_index_label()
	{
		$bsclass = $this->get_bsclass();

		return [
			'index' => [
				$this->id, 
				$this->name, 
				\App\Http\Controllers\BaseViewController::translate_view($this->type, 'Ticket_Type'),
				\App\Http\Controllers\BaseViewController::translate_view($this->priority, 'Ticket_Priority'),
				\App\Http\Controllers\BaseViewController::translate_view($this->state, 'Ticket_State'),
				self::getUserName($this->user_id), 
				$this->created_at
			],
			'index_header' => ['Ticket Id', 'Title', 'Type', 'Priority', 'State', 'Created by', 'Created at'],
			'header' => $this->id . ' - ' . $this->name,
			'bsclass' => $bsclass
		];
	}

	public function view_index_label_ajax()
	{
		$bsclass = $this->get_bsclass();

		return [
			'table' => $this->table,
			'index_header' => [
				$this->table . '.id',
				$this->table . '.name',
				$this->table . '.type',
				$this->table . '.priority',
				$this->table . '.state',
				$this->table . '.user_id',
				$this->table . '.created_at'
			],
			'header' => $this->id . ' - ' . $this->name,
			'bsclass' => $bsclass,
			'order_by' => ['0' => 'desc'],
		];
	}

	public function get_bsclass()
	{
		$bs_class = 'default';

		if ($this->state == 'Closed') {
			$bs_class = 'success';
		} elseif ($this->state == 'In process') {
			$bs_class = 'info';
		} elseif ($this->state == 'New' && ($this->priority == 'Critical' || $this->priority == 'Major')) {
			$bs_class = 'danger';
		} elseif ($this->state == 'New' && ($this->priority == 'Trivial' || $this->priority == 'Minor')) {
			$bs_class = 'warning';
		}

		return $bs_class;
	}

	public function view_has_many()
	{
		$ret = array();

		// assigned comments
		$ret['Edit']['Comment']['class'] = 'Comment';
		$ret['Edit']['Comment']['relation'] = $this->comments;
		// assigned users
		$ret['Edit']['Assignee']['class'] = 'Assignee';
		$ret['Edit']['Assignee']['relation'] = $this->assignees;
		$ret['Edit']['Assignee']['options']['hide_create_button'] = 0;

		return $ret;
	}	
	
	public function comments()
	{
		return $this->hasMany('Modules\Ticketsystem\Entities\Comment')->orderBy('id', 'desc');
	}	
	
	public function assignees()
	{
		return $this->hasMany('Modules\Ticketsystem\Entities\Assignee');
	}

	private static function getUserName($id)
	{
		$user = \App\Authuser::find($id);
		return $user->first_name . ' ' . $user->last_name;
	}
}

class TicketObserver {

	public function creating($ticket)
	{
		// generate new ticketId and assign it to the ticket
		$last_id = $ticket::withTrashed()->count();
		$new_id = $last_id + 1;
		$ticket->id = $new_id;
		
		// add relation and remove assigned_user_id
		$this->add_to_ticketassignee($ticket);
		unset($ticket->assigned_user_id);
	}

	public function created($ticket)
	{
		$assigned_users = \DB::table('assignee')->where('ticket_id', '=', $ticket->id)->get();

		if (count($assigned_users) > 0) {
			foreach ($assigned_users as $assignee) {
				$user = \App\Authuser::find($assignee->user_id);
				$ticket = Ticket::find($assignee->ticket_id);

				if (!empty($user->email)) {
					\Mail::send('ticketsystem::emails.assignticket', ['user' => $user, 'ticket' => $ticket], function ($m) use ($user) {
			 	        $m->from('noreply@roetzer-engineering.com', 'NMS Prime');
			 	        $m->to($user->email, $user->last_name . ', ' . $user->first_name)->subject('New ticket assigned');
			        });
				}

			}
		}
	}

	public function updating($ticket)
	{
		// add relation and remove assigned_user_id
		$this->add_to_ticketassignee($ticket);
		unset($ticket->assigned_user_id);
	}

	private function add_to_ticketassignee($ticket)
	{
		if (trim($ticket->assigned_user_id) != '') {
			$assignees = explode(';', $ticket->assigned_user_id);

			foreach ($assignees as $assignee) {
				$entry = new Assignee;
				$entry->user_id = $assignee;
				$entry->ticket_id = $ticket->id;
				$entry->save();
			}
		}
	}
}
