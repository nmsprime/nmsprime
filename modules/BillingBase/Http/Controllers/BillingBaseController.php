<?php

namespace Modules\BillingBase\Http\Controllers;

use View;
use Schema;
use Illuminate\Support\Facades\Storage;
use Modules\ProvBase\Entities\Contract;
use App\Http\Controllers\BaseViewController;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\Dashboard\Entities\BillingAnalysis;

class BillingBaseController extends \BaseController
{
    public $name = 'BillingBase';

    public function index()
    {
        $title = 'Billing Dashboard';
        $income_data = BillingAnalysis::getIncomeData();
        $news = $this->news();

        return View::make('billingbase::index', $this->compact_prep_view(compact('title', 'income_data', 'news')));
    }

    public function view_form_fields($model = null)
    {
        $languages = BaseViewController::generateLanguageArray(BillingBase::getPossibleEnumValues('userlang'));

        $days[0] = null;
        for ($i = 1; $i < 29; $i++) {
            $days[$i] = $i;
        }

        // build data for mandate reference help string
        $contract = new Contract;
        $mandate = new SepaMandate;
        $cols1 = Schema::getColumnListing($contract->getTable());
        $cols2 = Schema::getColumnListing($mandate->getTable());
        $cols = array_merge($cols1, $cols2);

        foreach ($cols as $key => $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                unset($cols[$key]);
            }
        }

        $cols = implode(', ', $cols);

        return [
            ['form_type' => 'select', 'name' => 'userlang', 'description' => 'Language for settlement run', 'value' => $languages],
            ['form_type' => 'select', 'name' => 'currency', 'description' => 'Currency', 'value' => BillingBase::getPossibleEnumValues('currency')],
            ['form_type' => 'text', 'name' => 'tax', 'description' => 'Tax in %'],
            ['form_type' => 'select', 'name' => 'rcd', 'description' => 'Day of Requested Collection Date', 'value' => $days, 'help' => trans('helper.BillingBase.rcd')],
            ['form_type' => 'text', 'name' => 'mandate_ref_template', 'description' => 'Mandate Reference', 'help' => trans('helper.BillingBase.MandateRef').$cols, 'options' => ['placeholder' => \App\Http\Controllers\BaseViewController::translate_label('e.g.: String - {number}')]],
            ['form_type' => 'checkbox', 'name' => 'split', 'description' => 'Split Sepa Transfer-Types', 'help' => trans('helper.BillingBase.SplitSEPA'), 'space' => 1],

            ['form_type' => 'text', 'name' => 'cdr_offset', 'description' => trans('messages.cdr_offset'), 'help' => trans('helper.BillingBase.cdr_offset')],
            ['form_type' => 'text', 'name' => 'cdr_retention_period', 'description' => 'CDR retention period', 'help' => trans('helper.BillingBase.cdr_retention')],
            ['form_type' => 'text', 'name' => 'voip_extracharge_default', 'description' => trans('messages.voip_extracharge_default'), 'help' => trans('helper.BillingBase.extra_charge')],
            ['form_type' => 'text', 'name' => 'voip_extracharge_mobile_national', 'description' => trans('messages.voip_extracharge_mobile_national'), 'space' => 1],

            ['form_type' => 'checkbox', 'name' => 'fluid_valid_dates', 'description' => 'Uncertain start/end dates for tariffs', 'help' => trans('helper.BillingBase.fluid_dates')],
            ['form_type' => 'checkbox', 'name' => 'termination_fix', 'description' => 'Item Termination only end of month', 'help' => trans('helper.BillingBase.ItemTermination')],
            ['form_type' => 'checkbox', 'name' => 'show_ags', 'description' => trans('messages.show_ags'), 'help' => trans('helper.BillingBase.showAGs')],
        ];
    }

    /*
     * For News Blade:
     *
     * This function should guide a new user through critical stages
     * like installation. To do this, we should test how far installation
     * process is and addvice the next steps the user should do..
     *
     * This function could also be used to inform the user of new updates (etc)
     */
    public function news()
    {
        // check for insecure install
        if ($insecure = $this->isInsecureInstall()) {
            return $insecure;
        }

        // Install add sequence check
        if (\Module::collections()->has('ProvBase') && (\Modules\ProvBase\Entities\Modem::count() == 0)) {
            return $this->newsInstallAndSequenceCheck();
        }

        // Check for official news from support.nmsprime.com
        if ($news = $this->newsLoadOfficialSite()) {
            return $news;
        }

        // crowdin - check if language is still supported, otherwise show crowdin link
        if (! in_array(\Auth::user()->language, config('app.supported_locales'))) {
            return ['youtube' => 'https://www.youtube.com/embed/9mydbfHDDP4',
                'text' => ' <li>NMS PRIME is not yet translated to your language. Help translating NMS PRIME with
                    <a href="https://crowdin.com/project/nmsprime/'.\Auth::user()->language.'" target="_blank">Crowdin</a></li>', ];
        }

        // links need to be in embedded style, like:
        // return ['youtube' => 'https://www.youtube.com/embed/9mydbfHDDP4',
        //      'text' => "You should do: <a href=https://lifeisgood.com>BlaBlaBla</a>"];
    }

    /*
     * For News Blade:
     *
     * Check if installation is secure
     */
    private function isInsecureInstall()
    {
        // change default psw's
        if (\Hash::check('toor', \Auth::user()->password)) {
            return ['youtube' => 'https://www.youtube.com/embed/TVjJ7T8NZKw',
                'text' => '<li>Next: Change default Password! '.\HTML::linkRoute('User.profile', 'Global Config', \Auth::user()->id), ];
        }

        // check for insecure MySQL root password
        // This requires to run: mysql_secure_installation
        if (env('ROOT_DB_PASSWORD') == '') {
            try {
                \DB::connection('mysql-root')->getPdo();
                if (\DB::connection()->getDatabaseName()) {
                    return ['youtube' => 'https://www.youtube.com/embed/dZWjeL-LmG8',
                        'text' => '<li>Danger! Run: mysql_secure_installation in bash as root!', ];
                }
            } catch (\Exception $e) {
            }
        }

        // means: secure – nothing todo
    }

    /*
     * News panel: load news from support server to json file
     * Documentation panel: load documentation.json from support server
     *
     * Official News Parser
     */
    public function newsLoadToFile()
    {
        if (env('IGNORE_NEWS')) {
            return false;
        }

        // get actual network size based on SLA table
        $sla = \App\Sla::first();
        $support = 'https://support.nmsprime.com';

        $modemcount = 0;
        if (\Module::collections()->has('ProvBase')) {
            $modemcount = \Modules\ProvBase\Entities\Modem::count();
        }

        $files = [
            'news.json' => "$support/news.php?ns=".urlencode($sla->get_sla_size()).'&sla='.urlencode($sla->name).'&mc='.$modemcount,
            'documentation.json' => "$support/documentation.json",
        ];

        foreach ($files as $name => $url) {
            try {
                Storage::put("data/dashboard/$name", file_get_contents($url));
            } catch (\Exception $e) {
                Log::error("Error retrieving $name (using installed version): ".$e->getMessage());
                Storage::delete("data/dashboard/$name");
            }
        }
    }

    /*
     * For News Blade:
     *
     * Official News Parser
     */
    private function newsLoadOfficialSite()
    {
        $file = 'data/dashboard/news.json';

        if (! Storage::exists($file)) {
            return;
        }

        $json = json_decode(Storage::get($file));

        if (! isset($json->youtube) || ! isset($json->text)) {
            return;
        }

        return ['youtube' => $json->youtube,
            'text' => $json->text, ];
    }

    /*
     * For News Blade:
     *
     * check install sequence order
     */
    private function newsInstallAndSequenceCheck()
    {
        $text = '<li>'.trans('helper.next');
        // set ISP name
        if (! \GlobalConfig::first()->name) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s',
                'text' => $text.\HTML::linkRoute('Config.index', trans('helper.set_isp_name')), ];
        }

        // add CMTS
        if (\Modules\ProvBase\Entities\Cmts::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=159&',
                'text' => $text.\HTML::linkRoute('Cmts.create', trans('helper.create_cmts')), ];
        }

        // add CM and CPEPriv IP-Pool
        foreach (['CM', 'CPEPriv'] as $type) {
            if (\Modules\ProvBase\Entities\IpPool::where('type', $type)->count() == 0) {
                return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=240&',
                    'text' => $text.\HTML::linkRoute('IpPool.create', trans('helper.create_'.strtolower($type).'_pool'),
                            ['cmts_id' => \Modules\ProvBase\Entities\Cmts::first()->id, 'type' => $type]), ];
            }
        }

        // QoS
        if (\Modules\ProvBase\Entities\Qos::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=380&',
                'text' => $text.\HTML::linkRoute('Qos.create', trans('helper.create_qos')), ];
        }

        // Product
        if (\Module::collections()->has('BillingBase') &&
            \Modules\BillingBase\Entities\Product::where('type', '=', 'Internet')->count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=425&',
                'text' => $text.\HTML::linkRoute('Product.create', trans('helper.create_product')), ];
        }

        // Configfile
        if (\Modules\ProvBase\Entities\Configfile::where('device', '=', 'cm')->where('public', '=', 'yes')->count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/aYjuWXhaV3s?start=500&',
                'text' => $text.\HTML::linkRoute('Configfile.create', trans('helper.create_configfile')), ];
        }

        // add sepa account
        if (\Module::collections()->has('BillingBase') && \Modules\BillingBase\Entities\SepaAccount::count() == 0) {
            return ['text' => $text.\HTML::linkRoute('SepaAccount.create', trans('helper.create_sepa_account'))];
        }

        // add costcenter
        if (\Module::collections()->has('BillingBase') && \Modules\BillingBase\Entities\CostCenter::count() == 0) {
            return ['text' => $text.\HTML::linkRoute('CostCenter.create', trans('helper.create_cost_center'))];
        }

        // add Contract
        if (\Modules\ProvBase\Entities\Contract::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/t-PFsy42cI0?start=0&',
                'text' => $text.\HTML::linkRoute('Contract.create', trans('helper.create_contract')), ];
        }

        // check if nominatim email address is set, otherwise osm geocoding won't be possible
        if (env('OSM_NOMINATIM_EMAIL') == '') {
            return ['text' => $text.trans('helper.create_nominatim')];
        }

        // check if E-mails and names are set in Global Config Page/.env for Ticket module
        if ($text = $this->checkTicketSettings()) {
            return $text;
        }

        // check for local nameserver
        preg_match('/^Server:\s*(\d{1,3}).\d{1,3}.\d{1,3}.\d{1,3}$/m', shell_exec('nslookup nmsprime.com'), $matches);
        if (isset($matches[1]) && $matches[1] != '127') {
            return ['text' => $text.trans('helper.create_nameserver')];
        }

        // add Modem
        if (\Modules\ProvBase\Entities\Modem::count() == 0) {
            return ['youtube' => 'https://www.youtube.com/embed/t-PFsy42cI0?start=40&',
                'text' => $text.\HTML::linkRoute('Contract.edit', trans('helper.create_modem'), \Modules\ProvBase\Entities\Contract::first()), ];
        }

        return false;
    }

    /**
     * Check if the User can send/receive E-mails via Ticketsystem.
     *
     * @author Roy Schneider
     */
    private function checkTicketSettings()
    {
        // set variables in .env
        if (env('MAIL_HOST') == null || env('MAIL_USERNAME') == null || env('MAIL_PASSWORD') == null) {
            return ['text' => '<li> '.trans('helper.mail_env').' </li>'];
        }

        // set noreply name and address in Global Config Page
        $globalConfig = \GlobalConfig::first();

        if (Module::collections()->has('Ticketsystem') && (empty($globalConfig->noReplyName) || empty($globalConfig->noReplyMail))) {
            return ['text' => '<li>'.trans('helper.ticket_settings').'</li>'];
        }
    }
}
