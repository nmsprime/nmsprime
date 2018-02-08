<?php

namespace Modules\provbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Endpoint;
use Modules\ProvBase\Entities\Cmts;
use Modules\ProvBase\Entities\ProvBase;
use Modules\ProvVoip\Entities\Mta;

class dhcpCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nms:dhcp';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'make the DHCP config';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}



	/**
	 * Execute the console command - Create global Config & all Entries for Modems, Endpoints & Mtas to get an IP from Server
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Global Config part
		ProvBase::first()->make_dhcp_glob_conf();

		Modem::make_dhcp_cm_all();
		Endpoint::make_dhcp();

		if (\PPModule::is_active('provvoip')) {
			Mta::make_dhcp_mta_all();
		}


		// CMTS's
		Cmts::del_cmts_includes();
		foreach (Cmts::all() as $cmts)
			$cmts->make_dhcp_conf();

		// Restart dhcp server
		$dir = storage_path('systemd/');
		if (!is_dir($dir))
		{
			mkdir($dir, 0700, true);
			chown($dir, 'apache');
		}
		touch($dir.'dhcpd');

	}



}
