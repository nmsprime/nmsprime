<?php

namespace Modules\ProvBase\Entities;

use Request;

class Endpoint extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'endpoint';

    public function rules()
    {
        $id = $this->id;
        $modem = $this->exists ? $this->modem : Modem::with('configfile')->find(Request::get('modem_id'));
        $macRequiredRule = $modem->configfile->device == 'tr069' ? '' : '|required';

        return [
            'mac' => 'mac|unique:endpoint,mac,'.$id.',id,deleted_at,NULL'.$macRequiredRule,
            'hostname' => 'required|regex:/^(?!cm-)(?!mta-)[0-9A-Za-z\-]+$/|unique:endpoint,hostname,'.$id.',id,deleted_at,NULL',
            'ip' => 'nullable|required_if:fixed_ip,1|ip|unique:endpoint,ip,'.$id.',id,deleted_at,NULL',
        ];
    }

    // Name of View
    public static function view_headline()
    {
        return 'Endpoints';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-map-marker"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();

        return ['table' => $this->table,
            'index_header' => [$this->table.'.hostname', $this->table.'.mac', $this->table.'.ip', $this->table.'.description'],
            'header' =>  $this->label(),
            'bsclass' => $bsclass, ];
    }

    public function get_bsclass()
    {
        $bsclass = 'success';

        return $bsclass;
    }

    public function label()
    {
        $label = $this->hostname.' ';
        $labelExt = [];

        if ($this->mac) {
            $labelExt[] = $this->mac;
        }

        if ($this->fixed_ip && $this->ip) {
            $labelExt[] = $this->ip;
        }

        $label .= $labelExt ? '('.implode(' / ', $labelExt).')' : '';

        return $label;
    }

    public function view_belongs_to()
    {
        return $this->modem;
    }

    /**
     * all Relations:
     */
    public function modem()
    {
        return $this->belongsTo(Modem::class);
    }

    public function netGw()
    {
        $query = NetGw::join('ippool as i', 'netgw.id', 'i.netgw_id')
            ->where('i.type', 'CPEPub')
            ->whereRaw('INET_ATON("'.$this->ip.'") BETWEEN INET_ATON(i.ip_pool_start) AND INET_ATON(i.ip_pool_end)')
            ->select('netgw.*', 'i.net', 'i.netmask', 'i.ip_pool_start', 'i.ip_pool_end');

        return new \Illuminate\Database\Eloquent\Relations\BelongsTo($query, new NetGw, null, 'deleted_at', null);
    }

    public function nsupdate($del = false)
    {
        $cmd = '';
        $zone = ProvBase::first()->domain_name;

        if ($del) {
            if ($this->getOriginal('fixed_ip') && $this->getOriginal('ip')) {
                $rev = implode('.', array_reverse(explode('.', $this->getOriginal('ip'))));
                $cmd .= "update delete {$this->getOriginal('hostname')}.cpe.$zone.\nsend\n";
                $cmd .= "update delete $rev.in-addr.arpa.\nsend\n";
            } else {
                $mangle = exec("echo '{$this->getOriginal('mac')}' | tr -cd '[:xdigit:]' | xxd -r -p | openssl dgst -sha256 -mac hmac -macopt hexkey:$(cat /etc/named-ddns-cpe.key) -binary | python -c 'import base64; import sys; print(base64.b32encode(sys.stdin.read())[:6].lower())'");
                $cmd .= "update delete {$this->getOriginal('hostname')}.cpe.$zone.\nsend\n";
                $cmd .= "update delete $mangle.cpe.$zone.\nsend\n";
            }
        } else {
            if ($this->fixed_ip && $this->ip) {
                // endpoints with a fixed-address will get an A and PTR record (ip <-> hostname)
                $rev = implode('.', array_reverse(explode('.', $this->ip)));
                $cmd .= "update add $this->hostname.cpe.$zone. 3600 A $this->ip\nsend\n";
                $cmd .= "update add $rev.in-addr.arpa. 3600 PTR $this->hostname.cpe.$zone.\nsend\n";
                if ($this->add_reverse) {
                    $cmd .= "update add $rev.in-addr.arpa. 3600 PTR $this->add_reverse.\nsend\n";
                }
            } else {
                // other endpoints will get a CNAME record (hostname -> mangle)
                // mangle name is based only on cpe mac address
                $mangle = exec("echo '$this->mac' | tr -cd '[:xdigit:]' | xxd -r -p | openssl dgst -sha256 -mac hmac -macopt hexkey:$(cat /etc/named-ddns-cpe.key) -binary | python -c 'import base64; import sys; print(base64.b32encode(sys.stdin.read())[:6].lower())'");
                $cmd .= "update add $this->hostname.cpe.$zone. 3600 CNAME $mangle.cpe.$zone.\nsend\n";
            }
        }

        $pw = env('DNS_PASSWORD');
        $handle = popen("/usr/bin/nsupdate -v -l -y dhcpupdate:$pw", 'w');
        fwrite($handle, $cmd);
        pclose($handle);
    }

    /**
     * BOOT:
     * - init modem observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new EndpointObserver);
        self::observe(new \App\SystemdObserver);
    }

    /**
     * Make DHCP config files for EPs
     */
    public static function make_dhcp()
    {
        $dir = '/etc/dhcp-nmsprime/';
        $file_ep = $dir.'endpoints-host.conf';

        $data = '';

        foreach (self::whereNotNull('mac')->get() as $ep) {
            $data .= "host $ep->hostname { hardware ethernet $ep->mac; ";
            if ($ep->fixed_ip && $ep->ip) {
                $data .= "fixed-address $ep->ip; ";
            }
            $data .= "}\n";
        }

        $ret = file_put_contents($file_ep, $data, LOCK_EX);
        if ($ret === false) {
            exit('Error writing to file');
        }

        // chown for future writes in case this function was called from CLI via php artisan nms:dhcp that changes owner to 'root'
        system('/bin/chown -R apache /etc/dhcp-nmsprime/');

        return $ret > 0;
    }

    /**
     * Get next hostname for a new Endpoint that shall be created via GUI
     *
     * @author Nino Ryschawy
     * @return string  e.g. cpe-100010-2 | null when used in place where Request doesn't contain modem_id
     */
    public static function getNewHostname()
    {
        if (! Request::has('modem_id')) {
            return;
        }

        $modem = Modem::find(Request::get('modem_id'));
        $default = 'cpe-'.$modem->id;

        if ($modem->endpoints->isEmpty()) {
            return $default;
        }

        $lastHostname = $modem->endpoints->filter(function ($item) use ($default) {
            if (strpos($item->hostname, $default.'-') !== false) {
                return $item;
            }
        })->pluck('hostname')->sort()->last();

        if (! $lastHostname) {
            return $default.'-2';
        }

        return $default.'-'.(substr(strrchr($lastHostname, '-'), 1) + 1);
    }
}

class EndpointObserver
{
    public function creating($endpoint)
    {
        if (! $endpoint->fixed_ip) {
            $endpoint->ip = null;
        }
    }

    public function created($endpoint)
    {
        self::reserveAddress($endpoint);

        $endpoint->make_dhcp();
        if ($endpoint->netGw) {
            $endpoint->netGw->make_dhcp_conf();
        }
        $endpoint->nsupdate();
    }

    public function updating($endpoint)
    {
        if (! $endpoint->fixed_ip) {
            $endpoint->ip = null;
        }
        $endpoint->nsupdate(true);
    }

    public function updated($endpoint)
    {
        self::reserveAddress($endpoint);

        $endpoint->make_dhcp();
        if ($endpoint->netGw) {
            $endpoint->netGw->make_dhcp_conf();
        }
        $endpoint->nsupdate();
    }

    public function deleted($endpoint)
    {
        self::reserveAddress($endpoint);

        $endpoint->make_dhcp();
        if ($endpoint->netGw) {
            $endpoint->netGw->make_dhcp_conf();
        }
        $endpoint->nsupdate(true);
    }

    /**
     * Handle changes of reserved ip addresses based on endpoints
     * This is called on created/updated/deleted in Endpoint observer
     *
     * @author Ole Ernst
     */
    private static function reserveAddress($endpoint)
    {
        // delete radreply containing Framed-IP-Address
        $endpoint->modem->radreply()->delete();

        // reset state of original ip address
        RadIpPool::where('framedipaddress', $endpoint->getOriginal('ip'))
            ->update(['expiry_time' => null, 'username' => '']);

        if ($endpoint->deleted_at || ! $endpoint->ip || ! $endpoint->modem->isPPP()) {
            return;
        }

        // add new radreply
        $reply = new RadReply;
        $reply->username = $endpoint->modem->ppp_username;
        $reply->attribute = 'Framed-IP-Address';
        $reply->op = ':=';
        $reply->value = $endpoint->ip;
        $reply->save();

        // set expiry_time to 'infinity' for reserved ip addresses
        RadIpPool::where('framedipaddress', $endpoint->ip)
            ->update(['expiry_time' => '9999-12-31 23:59:59', 'username' => $endpoint->modem->ppp_username]);
    }
}
