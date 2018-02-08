<?php

namespace Modules\Ticketsystem\Entities;

class Ticket extends \BaseModel {

	public $table = 'ticket';

	public $guarded = ['users_ids', 'tickettypes_ids'];

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


	public function view_index_label()
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
				$this->table . '.created_at',
				$this->table . '.assigned_users',
			],
			'header' => $this->id . ' - ' . $this->name,
			'bsclass' => $bsclass,
			'order_by' => ['0' => 'desc'],
			'edit' => ['assigned_users' => 'get_assigned_users',
				'type' => 'index_types',
				'user_id' => 'username'],
		];
	}

	/**
	 * Index View Column Manipulation Functions
	 */

	/**
	 * Concatenate names of assigned users for index view
	 * @return String
	 */
	public function get_assigned_users()
	{
		$string = '';
		foreach ($this->users as $user)
		{
			$string .= $string ? ', ' : '';

			if (strlen($string) > 125)
				return $string.'...';

			$string .= $user->first_name.' '.$user->last_name;
		}

		return $string;
	}

	/**
	 * @return String
	 */
	public function index_types()
	{
		return $this->tickettypes ? implode(', ', $this->tickettypes->pluck('name')->all()) : '';
	}

	public function username()
	{
		return $this->user ? $this->user->first_name.' '.$this->user->last_name : '';
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

		// assigned users
		// $ret['Edit']['Authuser']['class'] = 'Authuser';
		// $ret['Edit']['Authuser']['relation'] = $this->users;
		// $ret['Edit']['Authuser']['options']['many_to_many'] = 'users';
		// $ret['Edit']['Authuser']['options']['hide_create_button'] = true;
		// assigned comments
		$ret['Edit']['Comment']['class'] = 'Comment';
		$ret['Edit']['Comment']['relation'] = $this->comments;

		return $ret;
	}

	/**
	 * Relations
	 */
	public function comments()
	{
		return $this->hasMany('Modules\Ticketsystem\Entities\Comment')->orderBy('id', 'desc');
	}

	// user who created the ticket
	public function user()
	{
		return $this->belongsTo('App\Authuser', 'user_id');
	}

	// assigned users
	public function users()
	{
		return $this->belongsToMany('\App\Authuser', 'ticket_user', 'ticket_id', 'user_id');
	}

	public function contract()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract');
	}

	public function tickettypes()
	{
		return $this->belongsToMany('Modules\Ticketsystem\Entities\TicketType', 'tickettype_ticket', 'ticket_id', 'tickettype_id');
	}


	/**
	 * Return list of Users that are not yet assigned to this Ticket
	 */
	// public function not_assigned_users()
	// {
	// 	$ret = array();
	// 	$users = \App\Authuser::orderBy('last_name', 'ASC')->get();
	// 	$users_assigned = $this->users;

	// 	foreach ($users as $user)
	// 	{
	// 		if (!$users_assigned->contains('id', $user->id))
	// 			$ret[] = $user;
	// 	}

	// 	return $ret;
	// }

}


class TicketObserver
{
	public function creating($ticket)
	{
		$ticket->duedate = $ticket->duedate ? : null;
	}

	public function created($ticket)
	{
		foreach ($ticket->users as $user)
		{
			// send mail to assigned users
			if (empty($user->email))
				continue;

			\Mail::send('ticketsystem::emails.assignticket', ['user' => $user, 'ticket' => $ticket], function ($m) use ($user, $ticket) {
				$m->from('noreply@roetzer-engineering.com', 'NMS Prime');
				$m->to($user->email, $user->last_name . ', ' . $user->first_name)->subject(trans('new_ticket')."\n\n".$ticket->description);
			});
		}
	}

	public function updating($ticket)
	{
		$ticket->duedate = $ticket->duedate ? : null;
	}

	public function updated($ticket)
	{
		// TODO: send mail, too
	}

}
