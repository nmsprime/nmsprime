<?php 

namespace Modules\Ticketsystem\Entities;

class Comment extends \BaseModel {

	protected $table = 'comment';

    public static function view_headline()
	{
		return 'Comments';
	}

	public static function view_icon()
	{
		return '<i class="fa fa-commenting-o"></i>';
	}

	public function index_list()
	{
		return $this->orderBy('id', 'desc')->get();
	}

	public function view_index_label()
	{
		return [
			// 'index' => [
			// 	$this->id, 
			// 	$this->comment, 
			// ],
//			'index_header' => ['Kommentar'],
			'header' => $this->id . ' - ' . substr_replace($this->comment, '...', 25)
		];
	}

	public function view_belongs_to ()
	{
		return $this->ticket;
	}

	/**
	 * Relation views
	 */
	public function ticket()
	{
		return $this->belongsTo('Modules\Ticketsystem\Entities\Ticket', 'ticket_id');
	}
}
