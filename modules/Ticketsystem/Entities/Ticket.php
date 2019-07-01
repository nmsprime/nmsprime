<?php

namespace Modules\Ticketsystem\Entities;

use Request;
use App\User;

class Ticket extends \BaseModel
{
    public $table = 'ticket';

    public $guarded = ['users_ids', 'tickettypes_ids'];

    public static function boot()
    {
        parent::boot();
        self::observe(new TicketObserver);
    }

    public static function view_headline()
    {
        return 'Tickets';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-ticket"></i>';
    }

    public static function rules($id = null)
    {
        return [
            'users_ids' => 'required',
        ];
    }

    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();

        return [
            'table' => $this->table,
            'index_header' => [
                $this->table.'.id',
                $this->table.'.name',
                'tickettypes.name',
                $this->table.'.priority',
                $this->table.'.state',
                $this->table.'.user_id',
                $this->table.'.created_at',
                'assigned_users',
            ],
            'header' => "$this->id - $this->name ($this->created_at)",
            'bsclass' => $bsclass,
            'order_by' => ['0' => 'desc'],
            'eager_loading' => ['tickettypes'],
            'edit' => ['assigned_users' => 'get_assigned_users',
                'tickettypes.name' => 'index_types',
                'user_id' => 'username', ],
            // 'filter' => ['tickettypes.name' => $this->tickettype_names_query()],
            'disable_sortsearch' => ['assigned_users' => 'false'],
        ];
    }

    /**
     * Index View Column Manipulation Functions
     */

    /**
     * Concatenate names of assigned users for index view
     * @return string
     */
    public function get_assigned_users()
    {
        $string = '';
        foreach ($this->users as $user) {
            $string .= $string ? ', ' : '';

            if (strlen($string) > 125) {
                return $string.'...';
            }

            $string .= $user->first_name.' '.$user->last_name;
        }

        return $string;
    }

    /**
     * @return string
     */
    public function index_types()
    {
        return $this->tickettypes ? implode(', ', $this->tickettypes->pluck('name')->all()) : '';
    }

    public function username()
    {
        return $this->user ? $this->user->first_name.' '.$this->user->last_name : '';
    }

    public function tickettype_names_query()
    {
        return "group_concat(tickettypes.names separator ', ') like ?";
        // from ticket t, tickettype_ticket ttt, tickettype tt where t.id=ttt.ticket_id and ttt.tickettype_id=tt.id";
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

    /*
     * Relation Views
     */
    public function view_has_many()
    {
        $ret = [];

        $ret['Edit']['Comment']['class'] = 'Comment';
        $ret['Edit']['Comment']['relation'] = $this->comments;

        return $ret;
    }

    public function view_belongs_to()
    {
        return $this->contract;
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
        return $this->belongsTo('App\User', 'user_id');
    }

    // assigned users
    public function users()
    {
        return $this->belongsToMany(User::class, 'ticket_user', 'user_id', 'ticket_id');
    }

    public function contract()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Contract');
    }

    public function tickettypes()
    {
        return $this->belongsToMany(TicketType::class, 'tickettype_ticket', 'tickettype_id', 'ticket_id');
    }

    /**
     * Send Email to all assigned users from noreply@roetzer-engineering.com.
     * See: .env and mail.php
     *
     * @author Roy Schneider
     */
    public function mailAssignedUsers($ticketUsers)
    {
        // init
        $author = ['0' => $this->user_id];
        $input = $ticketUsers;
        $subject = trans('messages.ticketUpdated', ['id' => $this->id]);
        $ticketAssigned = trans('messages.ticketUpdatedMessage');
        $ids = $this->users->pluck('id')->toArray();
        $settings = $this->validGlobalSettings();

        // creator of the ticket should get an email too
        // creator and editor can be 2 different users
        if (isset($ticketUsers)) {
            array_merge($ticketUsers, $author);
        }

        // get collection of users
        $users = $this->getTicketUsers($ticketUsers);

        // the user that edits the ticket shouldn't reveive an email
        $author = $users->pluck('id')->search(\Auth::user()->id);
        if (is_int($author)) {
            $users->forget($author);
        }

        foreach ($users as $user) {
            // send mail to assigned users and if more than only updated_at has changed
            if (empty($user->email) || ($ids == $input && ! $this->importantChanges())) {
                continue;
            }

            // message for new ticket
            if (! $this->created_at || ! in_array($user->id, $ids)) {
                $subject = trans('messages.newTicket');
                $ticketAssigned = trans('messages.newTicketAssigned');
            }

            \Mail::send('ticketsystem::emails.assignticket', ['user' => $user, 'ticket' => $this, 'ticketAssigned' => $ticketAssigned],
                function ($message) use ($user, $subject, $settings) {
                    $message->from($settings['noReplyMail'], $settings['noReplyName'])
                            ->to($user->email, $user->last_name.', '.$user->first_name)
                            ->subject($subject);
                });
        }
    }

    /**
     * Send Email to users who were deleted from the ticket.
     *
     * @author Roy Schneider
     * @param  array
     */
    public function mailDeletedTicketUsers($deletedUsers)
    {
        $subject = trans('messages.deletedTicketUsers', ['id' => $this->id]);
        $settings = $this->validGlobalSettings();

        // get collection of users
        $users = $this->getTicketUsers($deletedUsers);

        // the user that edits the ticket shouldn't reveive an email
        $author = $users->pluck('id')->search(\Auth::user()->id);
        if (is_int($author)) {
            $users->forget($author);
        }

        foreach ($users as $user) {
            if (empty($user->email)) {
                continue;
            }

            \Mail::raw(trans('messages.deletedTicketUsersMessage', ['id' => $this->id]),
                function ($message) use ($user, $subject, $settings) {
                    $message->from($settings['noReplyMail'], $settings['noReplyName'])
                            ->to($user->email, $user->last_name.', '.$user->first_name)
                            ->subject($subject);
                });
        }
    }

    /**
     * Compare with previous input.
     * Check if there are changes in name, state, priority, duedate, users_ids and description.
     *
     * @author Roy Schneider
     * @return mixed values
     */
    public function importantChanges()
    {
        $changes = Request::all();

        if ($changes['description'] == $this->getOriginal('description')
            && $changes['state'] == $this->getOriginal('state')
            && $changes['priority'] == $this->getOriginal('priority')
            && $changes['duedate'] == $this->getOriginal('duedate')
            && $changes['name'] == $this->getOriginal('name')) {
            return false;
        }

        return true;
    }

    /**
     * Find all valid Users of a Ticket
     *
     * @author Roy Schneider
     * @param array $ticketUsers
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getTicketUsers($ticketUsers)
    {
        return User::find($ticketUsers);
    }

    /**
     * Get app/GlobalConfig.php and check if noReplyName and noReplyMail are set.
     *
     * @author Roy Schneider
     * @return array
     */
    public function validGlobalSettings()
    {
        $config = \App\GlobalConfig::first();

        if (empty($config->noReplyName) || empty($config->noReplyMail)) {
            abort('422', trans('view.error_ticket_settings'));
        }

        return ['noReplyName' => $config->noReplyName, 'noReplyMail' => $config->noReplyMail];
    }
}

class TicketObserver
{
    public function created($ticket)
    {
        $ticket->duedate = $ticket->duedate ?: null;

        // get assigned users and previously assigned users
        $input = Request::get('users_ids');
        $ticket->mailAssignedUsers($input);
    }

    public function updating($ticket)
    {
        $ticket->duedate = $ticket->duedate ?: null;

        // get assigned users and previously assigned users
        $ticketUsers = $ticket->users;
        $input = Request::get('users_ids');

        // create array with user ids
        $users = $ticketUsers->pluck('id', 'id')->toArray() ?? [];

        // compare input and saved users
        if ($input !== null && ! empty(array_diff($users, $input))) {
            $deletedUser = array_diff($users, $input);
            $ticket->mailDeletedTicketUsers($deletedUser);

            foreach ($deletedUser as $key => $id) {
                unset($users[$key]);
            }
        }

        if ($ticket->importantChanges()) {
            $ticket->mailAssignedUsers($users);
        }

        if ($newUsers = array_diff($input, $users)) {
            $ticket->mailAssignedUsers($newUsers);
        }
    }
}
