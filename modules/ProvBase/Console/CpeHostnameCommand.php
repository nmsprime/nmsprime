<?php

namespace Modules\ProvBase\Console;

use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Endpoint;
use Modules\ProvBase\Entities\ProvBase;

class CpeHostnameCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nms:cpe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate CPE hostnames';

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
     * Execute the console command
     *
     * @return mixed
     */
    public function handle()
    {
        $pw = env('DNS_PASSWORD');
        $domain = ProvBase::first()->domain_name;
        $zones = array_map(function ($zone) {
            return basename($zone, '.zone');
        }, glob('/var/named/dynamic/*in-addr.arpa.zone'));

        $zones[] = $domain;

        // remove all .cpe.$domain forward and reverse DNS entries
        foreach ($zones as $zone) {
            $cmd = shell_exec("dig -tAXFR $zone | grep '\.cpe\.$domain.' | awk '{ print \"update delete\", $1 }'; echo send");
            $handle = popen("/usr/bin/nsupdate -v -l -y dhcpupdate:$pw", 'w');
            fwrite($handle, $cmd);
            pclose($handle);
        }

        // get all active leases
        preg_match_all('/^lease(.*?)(^})/ms', file_get_contents('/var/lib/dhcpd/dhcpd.leases'), $leases);
        // get the required parameters and run named-ddns.sh for every cpe
        foreach ($leases[0] as $lease) {
            if (preg_match('/;\s*binding state active;.*set ip = "([^"]*).*set hw_mac = "([^"]*)/s', $lease, $match)) {
                $octets = explode('.', $match[1]);
                if ((! filter_var($match[1], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) ||
                    ($octets[0] == 100 && $octets[1] >= 64 && $octets[1] <= 127)) {
                    continue;
                }
                exec("/etc/named-ddns.sh $match[2] $match[1] 0\n");
            }
        }

        // add forward and reverse DNS entries for all endpoints
        foreach (Endpoint::all() as $endpoint) {
            $endpoint->nsupdate();
        }
    }
}
