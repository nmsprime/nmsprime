<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use \Chumper\Zipper\Zipper;
use Storage;
use File;
use Modules\BillingBase\Entities\BillingLogger;
use Modules\BillingBase\Entities\BillingBase;

class cdrCommand extends Command {

	/**
	 * The console command & table name
	 *
	 * @var string
	 */
	protected $name 		= 'billing:cdr';
	protected $description 	= 'Get Call Data Records from Envia/HLKomm (dependent of Array keys in Environment file) - optional argument: month (integer - load file up to 12 months in past)';
	protected $signature 	= 'billing:cdr {month? : 1 (Jan) to 12 (Dec)}';


	/**
	 * Self defined global Variables - set by _init()
	 	* date we want the call data records for
	 	* directories and filenames
	 */
	protected $month = '';
	protected $year  = '';

	protected $tmp_dir 		= '';
	protected $target_dir 	= '';
	protected $target_file  = '';


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
	 * Execute the console command - Get CSV from Provider Interface if not yet done
	 */
	public function fire()
	{
		$this->_init();

		// skip if file is already loaded
		if (is_file($this->target_dir.$this->target_file))
		{
			$logger = new BillingLogger;
			$logger->addDebug('CDR already loaded');
			return;
		}


		// Choose Provider from specified array key in environment file
		// NOTE: Add new Providers here!!!
		if (isset($_ENV['PROVVOIPENVIA__RESELLER_USERNAME']))
		{
			$this->_get_envia_cdr();
		}

		else if (isset($_ENV['HLKOMM_RESELLER_USERNAME']))
		{
			$this->_get_hlkomm_cdr();
		}

		else
			throw new Exception('Missing Reseller Data in Environment File!');

		// chown in case command was called from commandline as root
		system('chown -R apache '.storage_path('app/data/billingbase/'));

	}


	/**
	 * Init global variables
		* logger
		* dates
		* directory- & filepaths
	 */
	private function _init()
	{
		$offset = BillingBase::first()->cdr_offset;
		$month_sec = 31*24*60*60;

		if ($this->argument('month') >= 1 && $this->argument('month') <= 12)
			// argument is specified
			$this->month = sprintf('%02d', $this->argument('month'));
		else
			$this->month = $offset ? date('m', strtotime('-'.($offset+1).' month')) : date('m', strtotime('first day of last month'));

		// $this->month = $this->argument('month') >= 1 && $this->argument('month') <= 12 ? sprintf('%02d', $this->argument('month')) : ( $offset == 0 ? date('m', strtotime('first day of last month')) : date('m', strtotime('-'.($offset+1).' month')));
		$this->year  = $this->month >= date('m') ? date('Y') - 1 : date('Y');

		$this->tmp_dir 		= storage_path('app/tmp/');
		$this->target_dir   = storage_path("app/data/billingbase/accounting/");
		$this->target_dir 	.= $offset ? date('Y-m', $offset * $month_sec + strtotime('01.'.$this->month.'.'.$this->year)) : $this->year.'-'.$this->month;
		$this->target_file  = \App\Http\Controllers\BaseViewController::translate_label('Call Data Record')."_".$this->year.'_'.$this->month.'.csv';
	}


	/**
	 * Load Call Data Records from Envia Interface and save file to accounting directory of appropriate date
	 *
 	 * @return integer 		0 on success, -1 on error
	 */
	private function _get_envia_cdr()
	{
		$user 	  = $_ENV['PROVVOIPENVIA__RESELLER_USERNAME'];
		$password = $_ENV['PROVVOIPENVIA__RESELLER_PASSWORD'];
		$logger = new BillingLogger;

		// TODO: proof if file is already available
		$data = file_get_contents("https://$user:$password@www.enviatel.de/portal/vertrieb2/reseller/evn/K8000002961/".$this->year.'/'.$this->month);
		if (!$data)
		{
			$logger->addAlert('CDR-Import: Could not get Call Data Records from Envia for month: '.$this->month, ["www.enviatel.de/portal/vertrieb2/reseller/evn/K8000002961/2016/$month"]);
			return -1;
		}

		$tmp_file = 'cdr.zip';
		Storage::put("tmp/$tmp_file", $data);

		if (!is_dir($this->target_dir))
			mkdir($this->target_dir, 0744, true);

		$zipper = new Zipper;
		$zipper->make($this->tmp_dir.$tmp_file)->extractTo($this->tmp_dir);
		
		// TODO: Rename File
		$files = Storage::files('tmp');
		foreach ($files as $name)
		{
			if (strpos($name, $this->month.'.'.$this->year) !== false && strpos($name, 'AsciiEVN.txt') !== false)
			{
				$target_file = $this->target_dir.'/'.$this->target_file;
				rename(storage_path('app/'.$name), $target_file);
				break;
			}
		}

		$logger->addDebug("Successfully stored Call Data Record in ".$this->target_dir, [$this->target_file]);

		Storage::delete("tmp/$tmp_file");
	}


	/**
	 * Load Call Data Records from HLKomm Interface and save to accounting directory of appropriate date
	 *
	 * @return integer 		0 on success, -1 on error
	 */
	private function _get_hlkomm_cdr()
	{
		$user 	  = $_ENV['HLKOMM_RESELLER_USERNAME'];
		$password = $_ENV['HLKOMM_RESELLER_PASSWORD'];
		$logger = new BillingLogger;
		// establish ftp connection and login
		$ftp_server = "ftp.hlkomm.net";

		$ftp_conn = ftp_connect($ftp_server);
		if (!$ftp_conn)
		{
			$logger->addError('Load-CDR: Could not establish ftp connection!', [__FUNCTION__]);
			return -1;
		}

		$login = ftp_login($ftp_conn, $user, $password);
		// enable passive mode for client-to-server connections
		ftp_pasv($ftp_conn, true);
		$file_list = ftp_nlist($ftp_conn, ".");
		// $file_list = ftp_rawlist($ftp_conn, ".");

		// find correct filename
		foreach ($file_list as $fname)
		{
			if (strpos($fname, date('Ym')) !== false && strpos($fname, '_EVN.txt') !== false)
			{
				$remote_fname = $fname;
				break;
			}
		}

		if (!isset($remote_fname))
		{
			$logger->addError('No CDR File on ftp Server that matches naming conventions', [__FUNCTION__]);
			return -1;			
		}

		// load file
		$target_file = $this->target_dir.'/'.$this->target_file;

		if (!is_dir($this->target_dir))
			mkdir($this->target_dir, 0744, true);

		if (ftp_get($ftp_conn, $target_file, $remote_fname, FTP_BINARY))
			$logger->addDebug('Successfully stored CDR txt', [$target_file]);
		else
		{
			$logger->addError('Could not Retrieve CDR File from ftp Server', [__FUNCTION__]);
			return -1;
		}

		ftp_close($ftp_conn);

	}





	/**
	 * Get the console command arguments / options
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		// return [
		// 	['month', InputArgument::OPTIONAL, '1 (Jan) to 12 (Dec)'],
		// ];
	}

	protected function getOptions()
	{
		return [
			// ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}