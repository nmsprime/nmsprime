<?php 

namespace Modules\Ticketsystem\Entities;

use Modules\Ticketsystem\Entities\Ticket;

class Assignee extends \BaseModel {

	public $table = 'assignee';

	/**
	 * BOOT:
	 * - init observer
	 */
	 public static function boot()
	 {
		 parent::boot();
 
		 Assignee::observe(new AssigneeObserver);
	 }

	public static function view_headline()
	{
		return 'Assignees';
	}

	public static function view_icon()
	{
		return '<i class="fa fa-address-book-o"></i>';
	}

	public function view_index_label()
	{
		return [
//			'index' => [$this->comment],
//			'index_header' => ['Kommentar'],
			'header' => self::get_user_name($this->user_id)
		];
	}

	public function view_belongs_to ()
	{
		return $this->ticket;
	}

	public function ticket()
	{
		return $this->belongsTo('Modules\Ticketsystem\Entities\Ticket', 'ticket_id');
	}

	public static function get_assignees($ticket_id)
	{
		$ret = array();
		$request = request()->toArray();
		$users = \App\Authuser::orderBy('last_name', 'ASC')->get();

		foreach ($users as $user) {
			if (!empty($user->last_name && !self::is_assigned($user->id, $ticket_id))) {
				$ret[] = $user;
			}
		}
		return $ret;
	}

	private static function get_user_name($id)
	{
		$user = \App\Authuser::find($id);
		return $user->first_name . ' ' . $user->last_name;
	}

	private static function is_assigned($user_id, $ticket_id) 
	{
		$ret = false;
		$result = \DB::table('assignee')
			->where('ticket_id', $ticket_id)
			->where('user_id', $user_id)
			->get();

		if (count($result) != 0) {
			$ret = true;
		}
		return $ret;
	}

	public static function getAssignesByTicket($ticket_id)
	{
		return \DB::table('assignee')
			->where('ticket_id', $ticket_id)
			->get();
	}
}

class AssigneeObserver {

	public function created($assignee)
	{
		// send email to assignee
		$user = \App\Authuser::find($assignee->user_id);
		$ticket = Ticket::find($assignee->ticket_id);

 		if (!empty($user->email)) {
			 \Mail::send('ticketsystem::emails.assignticket', ['user' => $user, 'ticket' => $ticket], function ($m) use ($user) {
			 	$m->from('noreply@roetzer-engineering.com', 'NMS Prime');
			 	$m->to($user->email, $user->last_name . ', ' . $user->first_name)->subject('New ticket assigned');
			 });
		}

		return \Redirect::back();
	}

	public function deleted($assignee)
	{
		$query = 'DELETE FROM assignee WHERE deleted_at IS NOT NULL;';
		\DB::delete($query);
	}
}
