<?php

namespace Modules\ProvBase\Entities;

use DB;
use Log;
use Schema;
use Modules\ProvVoip\Entities\Phonenumber;

class Configfile extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'configfile';

    public $guarded = ['firmware_upload', 'import'];

    // Add your validation rules here
    public function rules()
    {
        $id = $this->id;

        return [
            'name' => 'required_without:import|unique:configfile,name,'.$id.',id,deleted_at,NULL',
            'text' => 'docsis',
        ];
    }

    // Name of View
    public static function view_headline()
    {
        return 'Configfiles';
    }

    // Global View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-file-code-o"></i>';
    }

    // link title in index view
    public function view_index_label()
    {
        return $this->device.': '.$this->name;
    }

    // icon type for tree view
    public function get_icon_type()
    {
        return $this->device ?: 'default';
    }

    /**
     * BOOT:
     * - init configfile observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new ConfigfileObserver);
    }

    /**
     * Searches children of a parent configfile recursivly to build the whole tree structure of all confifgfiles
     *
     * @author Nino Ryschawy
     * @param bool variable - if 1 all modems and mtas that belong to the configfile (and their children) are built
     */
    public function search_children($build = 0)
    {
        $id = $this->id;
        // TODO: this should not be a database query
        $children = self::where('parent_id', $id)->get();
        $cf_tree = [];

        foreach ($children as $cf) {
            if ($build) {
                $cf->build_corresponding_configfiles();
                $cf->search_children(1);
            } else {
                array_push($cf_tree, $cf);
                array_push($cf_tree, $cf->search_children());
            }
        }

        return $cf_tree;
    }

    /**
     * Returns all available files (via directory listing)
     * @author Patrick Reichel
     */
    public static function get_files($folder)
    {
        // get all available files
        $files_raw = glob("/tftpboot/$folder/*");
        $files = [null => 'None'];
        // extract filename
        foreach ($files_raw as $file) {
            if (is_file($file)) {
                $parts = explode('/', $file);
                $filename = array_pop($parts);
                $files[$filename] = $filename;
            }
        }

        return $files;
    }

    /**
     * all Relationships
     *
     * Note: Should be plural on hasMany
     */
    public function modem()
    {
        return $this->hasMany(Modem::class);
    }

    public function mtas()
    {
        return $this->hasMany(\Modules\ProvVoip\Entities\Mta::class);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class);
    }

    /**
     * Internal Helper:
     *   Make Configfile Content for $this Object /
     *   without recursive objects
     *
     * @param sw_up 	Bool 	true if Software upgrade statement is already set -> then the next one is discarded (child CF has priority)
     */
    private function __text_make($device, $type, $sw_up = false)
    {
        // for cfs of type modem, mta or generic
        // get global config - provisioning settings
        $db_schemata ['provbase'][0] = Schema::getColumnListing('provbase');
        $provbase = ProvBase::get();

        // array to extend the configfile; e.g. for firmware
        $config_extensions = [];

        // normalize type
        $type = strtolower($type);
        // we need a device to make the config for
        if (! $device) {
            return false;
        }

        /*
         * all objects must be an array like a[xyz] = object
         *
         * INFO:
         * - variable names _must_ match tables_a[xyz] coloumn
         * - if modem sql relations are not valid a warning will
         *   be printed
         */

        // using the given type we decide what to do
        switch ($type) {

            // this is for modem's config files
            case 'modem':

                $modem = [$device];
                $qos = [$device->qos];

                // Set test data rate if no qos is assigned - 250 kbit/s (i.e. VoIP only)
                if (! $this->parent_id && ! $device->qos) {
                    $qos[0] = new Qos;
                    $qos[0]->id = 0;
                    $qos[0]->ds_rate_max_help = 250000;
                    $qos[0]->us_rate_max_help = 250000;

                    Log::warning("Modem $device->id has no qos assigned - use test data rate for Configfile.");
                }

                /*
                 * generate Table array with SQL columns
                 */
                $db_schemata ['modem'][0] = Schema::getColumnListing('modem');
                $db_schemata ['qos'][0] = Schema::getColumnListing('qos');

                // if there is a specific firmware: add entries for upgrade
                if ($this->firmware && ! $sw_up) {
                    // $server_ip = ProvBase::first()['provisioning_server'];
                    // array_push($config_extensions, "SnmpMibObject docsDevSwServerAddress.0 IPAddress $server_ip ; /* tftp server */");
                    array_push($config_extensions, 'SnmpMibObject docsDevSwFilename.0 String "fw/'.$this->firmware.'"; /* firmware file to download */');
                    array_push($config_extensions, 'SnmpMibObject docsDevSwAdminStatus.0 Integer 2; /* allow provisioning upgrade */');
                    // array_push($config_extensions, 'SwUpgradeServer $server_ip;');
                    array_push($config_extensions, 'SwUpgradeFilename "fw/'.$this->firmware.'";');
                    exec("openssl pkcs7 -print_certs -inform DER -in /tftpboot/fw/$this->firmware | openssl x509 -outform DER | xxd -p -c 254 | sed 's/^/MfgCVCData 0x/; s/$/;/'", $config_extensions);
                }

                break;

            // this is for mtas
            case 'mta':

                // same as above – arrays for later generic use
                // they have to match database table names
                $mta = [$device];
                // get description of table mtas
                $db_schemata['mta'][0] = Schema::getColumnListing('mta');

                // check if MTA is an AVM FritzBox to disable deactivated phonenumbers by rewriting a wrong password
                preg_match('/SnmpMibObject .*?.872.1.4.3.1.5.[\d] String/', $this->text, $hit);
                $avm = $hit ? true : false;

                // get Phonenumbers to MTA
                foreach (Phonenumber::where('mta_id', '=', $device->id)->orderBy('port')->get() as $phone) {
                    if (! $phone->active) {
                        $phone->active = 2;

                        // deactivate AVM FritzBox phonenumber via wrong password
                        if ($avm) {
                            $phone->password = 'deactivated number';
                        }
                    }

                    // use the port number as primary index key, so {phonenumber.number.1} will be the phone with port 1, not id 1 !
                    $phonenumber[$phone->port] = $phone;
                    // get description of table phonennumbers; one subarray per (possible) number
                    $db_schemata['phonenumber'][$phone->port] = Schema::getColumnListing('phonenumber');
                }

                break;

            // for Base
            case 'generic':
                break;

            case 'tr069':
                $modem = [$device];
                $db_schemata['modem'][0] = Schema::getColumnListing('modem');
                $qos = [$device->qos];
                $db_schemata['qos'][0] = Schema::getColumnListing('qos');

                if (! $device->mtas->first()) {
                    break;
                }
                foreach ($device->mtas->first()->phonenumbers as $phone) {
                    // use the port number as primary index key, so {phonenumber.number.1} will be the phone with port 1, not id 1 !
                    $phonenumber[$phone->port] = $phone;
                    // get description of table phonennumbers; one subarray per (possible) number
                    $db_schemata['phonenumber'][$phone->port] = Schema::getColumnListing('phonenumber');
                }
                break;

            // this is for unknown types – atm we do nothing
            default:
                return false;

        } // switch

        // Generate search and replace arrays
        $search = [];
        $replace = [];

        $i = 0;

        // lo all schemata; they can exist multiple times per table
        foreach ($db_schemata as $table => $columns_multiple) {
            // loop over all schema descriptions of the current table
            foreach ($columns_multiple as $j => $columns) {
                // use the data arrays created before, calling them by current table name
                // fill temporary replacement array with database values
                if (isset(${$table}[$j]->id)) {
                    // loop over each column and check if there is something to replace
                    // column is used generic to get values
                    foreach ($columns as $column) {
                        $search[$i] = '{'.$table.'.'.$column.'.'.$j.'}';
                        $replace[$i] = ${$table}[$j]->{$column};

                        $i++;
                    }
                } else {
                    Log::warning($type.' '.$device->hostname.' has no valid '.$table.' entry');
                }
            }
        }

        // DEBUG: var_dump ($search, $replace);
        /*
         * Search and Replace Configfile TEXT
         */
        $text = str_replace($search, $replace, $this->text ?? $device->text);

        $rows = explode("\n", $text);

        // finally: append extensions; they have to be an array with one entry per line
        $rows = array_merge($rows, $config_extensions);

        $result = '';
        $match = [];
        foreach ($rows as $row) {
            // Ignore all rows with {xyz} content which can not be replaced
            if (preg_match('/\\{[^\\{]*\\}/im', $row, $match) && ($row = self::_calc_eval($row, $match)) === null) {
                continue;
            }
            $result .= "\n\t".$row;
        }

        return $result;
    }

    /**
     * Check if mathematical expression in configfile is valid and can be evaluated.
     * If so, return string containing the row replaced by the result, otherwise null.
     *
     * @author Ole Ernst
     */
    protected static function _calc_eval($row, $match)
    {
        // allow left-over {} in case of raw genieacs commands,
        // since this might be valid code, e.g. {value: Date.now()}
        if (\Str::startsWith($row, 'raw;')) {
            return $row;
        }

        $match = trim($match[0], '{}');
        $ops = explode(',', $match);

        if (count($ops) != 3 || ! in_array($ops[1], ['+', '-', '*', '/', '?'])) {
            return;
        }

        /*
         * overwrite configfile variable {}, which couldn't be resolved
         * via the database with a default value (i.e. $ops[2])
         * syntax: {{phonenumber.active.1},?,2};
         */
        if ($ops[1] === '?') {
            $res = \Str::contains($ops[0], '}') ? $ops[2] : $ops[0];
        } elseif (is_numeric($ops[0]) || is_numeric($ops[2])) {
            try {
                $res = round(eval("return $ops[0] $ops[1] $ops[2];"));
            } catch (\Exception $e) {
                // e.g. divide by zero
                return;
            }
        }

        return preg_replace('/\\{.*\\}/im', $res, $row);
    }

    /**
     * Make Configfile Content
     */
    public function text_make($device, $type)
    {
        $p = $this;
        $t = '';
        $sw_up = false;

        do {
            $t .= $p->__text_make($device, $type, $sw_up);

            // only allow one sw upgrade statement
            if (strpos($t, 'SwUpgradeFilename "fw/') !== false && ! $sw_up) {
                $sw_up = true;
            }

            $p = $p->parent;
        } while ($p);

        return $t;
    }

    /**
     * Build the configfiles of the appropriate modems and mtas after a configfile was updated/created/assigned
     *
     * @author Nino Ryschawy
     */
    public function build_corresponding_configfiles()
    {
        $modems = $this->modem;
        foreach ($modems as $modem) {
            $modem->make_configfile();
        }

        $mtas = $this->mtas; // This should be a one-to-one relation
        foreach ($mtas as $mta) {
            $mta->make_configfile();
        }
    }

    /**
     * Recursively add all parents of a used node to the list of used nodes,
     * we must not delete any of them
     *
     * @author Ole Ernst
     */
    protected static function _add_parent(&$ids, $cf)
    {
        $parent = $cf->parent;
        if ($parent && ! in_array($parent->id, $ids)) {
            array_push($ids, $parent->id);
            self::_add_parent($ids, $parent);
        }
    }

    /**
     * Returns a list of configfiles (incl. all of its parents), which are
     * still assigned to a modem or mta and thus must not be deleted.
     *
     * @author Ole Ernst
     *
     * NOTE: DB::table would reduce time again by 30%, setting index_delete_disabled of CFs
     *       instead of creating used_ids array slows function down
     */
    public static function undeletables()
    {
        $used_ids = [];

        // only public configfiles can be assigned to a modem or mta
        foreach (self::where('public', 'yes')->withCount('modem', 'mtas')->with('parent')->get() as $cf) {
            if ((($cf->device != 'mta') && $cf->modem_count) || (($cf->device == 'mta') && $cf->mtas_count)) {
                array_push($used_ids, $cf->id);
                self::_add_parent($used_ids, $cf);
            }
        }

        return $used_ids;
    }

    /**
     * Creates all configfiles for all modems.
     * Logic from ConfigfileCommand nms:configfile.
     */
    public function execute($filter = null, $id = null)
    {
        \Log::debug("configfileCommand called with configfile id: $id");

        // handle configfile observer functionality via job in background
        if ($id) {
            $cf = self::find($id);
            $cf->build_corresponding_configfiles();
            $cf->search_children(1);

            return;
        }

        // Modem
        foreach (Modem::TYPES as $type) {
            if (! $filter || $filter == $type) {
                $modems = Modem::join('configfile', 'configfile.id', 'modem.configfile_id')
                    ->where('configfile.device', $type)
                    ->whereNull('configfile.deleted_at')
                    ->select('modem.*')
                    ->get();

                $this->build_configfiles($modems, $type);
            }
        }

        // MTA
        if (! $filter || $filter == 'mta') {
            if (! \Module::collections()->has('ProvVoip')) {
                return;
            }

            $mtas = \Modules\ProvVoip\Entities\Mta::all();
            $this->build_configfiles($mtas, 'mta');
        }
    }

    /**
     * @param array  Objects of Modem or Mta
     */
    public function build_configfiles($devices, $type)
    {
        $i = 1;
        $num = count($devices);
        $type = strtoupper($type);

        \Log::info("Build all $num $type configfiles");

        foreach ($devices as $device) {
            echo "$type: create config files: $i/$num \r";
            $i++;
            $device->make_configfile();
        }

        echo "\n";
    }

    /**
     * Get monitoring config based on the json string found in the configfile
     *
     * @author Ole Ernst
     */
    public function getMonitoringConfig()
    {
        if (! preg_match('/#monitoring:({.*})/', $this->text, $matches)) {
            return false;
        }

        $conf = json_decode($matches[1], true);
        if (! $conf) {
            return false;
        }

        return $conf;
    }
}

/**
 * Configfile Observer Class
 * Handles changes on CMs
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class ConfigfileObserver
{
    public function created($configfile)
    {
        $this->updateProvision($configfile, false);
        // When a Configfile was created we can not already have a relation - so dont call command
    }

    public function updated($configfile)
    {
        $this->updateProvision($configfile, false);

        \Queue::push(new \Modules\ProvBase\Jobs\ConfigfileJob(null, $configfile->id));
        // $configfile->build_corresponding_configfiles();
        // with parameter one the children are built
        // $configfile->search_children(1);
    }

    public function deleted($configfile)
    {
        $this->updateProvision($configfile, true);
        // Actually it's only possible to delete configfiles that are not related to any cm/mta - so no CFs need to be built

        // Make sure that undeleted children still show up in tree
        $childrenQuery = Configfile::where('parent_id', $configfile->id);
        $children = $childrenQuery->get();

        $childrenQuery->update(['parent_id' => $configfile->parent_id]);

        foreach ($children as $child) {
            \Queue::push(new \Modules\ProvBase\Jobs\ConfigfileJob(null, $child->id));
        }
    }

    /**
     * Update monitoring provision of the corresponding configfile.
     *
     * The provision is assigned to every tr069 device having this configfile.
     * This makes sure, that we retrieve all objects to be monitored during every PERIODIC INFORM.
     *
     * @author Ole Ernst
     */
    private function updateProvision($configfile, $deleted)
    {
        // always delete provision, GenieACS doesn't mind deleting non-exisiting provisions
        // this way we don't need to care for a dirty $configfile->device
        Modem::callGenieAcsApi("provisions/mon-$configfile->id", 'DELETE');

        // nothing to do
        if ($deleted || $configfile->device != 'tr069') {
            return;
        }

        $prov = [];
        $conf = $configfile->getMonitoringConfig() ?: [];

        $prov = array_map(function ($value) {
            if (\Str::startsWith($value, ['_', 'Device', 'InternetGatewayDevice'])) {
                return "declare('$value', {value: Date.now() - (290 * 1000)});";
            }
        }, \Illuminate\Support\Arr::flatten($conf));

        Modem::callGenieAcsApi("provisions/mon-$configfile->id", 'PUT', implode("\r\n", $prov));
    }
}
