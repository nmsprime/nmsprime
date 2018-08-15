<?php

namespace App\Console\Commands;

use Bouncer;
use App\Role;
use App\User;
use App\Ability;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/*
 * Set default rights for all Modules
 *
 * NOTE: Module Installation requires a technic to re-install or install some modules after
 *       basic "php artisan migrate" script will be run. Patrick set all default right via migration.
 *       But this will not work for update/re-installation of Modules. So this script will do this job
 *
 *       Example: ISP starts with only provbase module, after a year he needs provvoip. Only running
 *                module:migrate will not adapte the required Auth needed for access.
 *
 * NOTE: This Command could be used in the feature to adapt auth/access rights via command line.
 *
 * @autor Torsten Schmidt
 */
class authCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'auth:nms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update authentication tables and access rights';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->setVerbosity('v');

        Bouncer::allow('admin')->everything();
        Bouncer::unforbid('admin')->everything();

        $this->line('Admin Role resetted.');

        foreach (User::all() as $user) {
            Bouncer::allow($user)->toOwn(User::class);
        }

        //create the custom abilities
        $ability = Ability::firstOrNew([
            'name' => 'use api',
            'title' => 'Use api',
            'only_owned' => '0',
        ]);

        $this->line('Ability: "'.$ability->title.'" processed');

        $ability = Ability::firstOrNew([
            'name' => 'see income chart',
            'title' => 'See income chart',
            'only_owned' => '0',
        ]);

        $this->line('Ability: "'.$ability->title.'" processed');

        $ability = Ability::firstOrNew([
            'name' => 'view_analysis_pages_of',
            'title' => 'View analysis pages of modems',
            'entity_type' => \Modules\ProvBase\Entities\Modem::class,
            'only_owned' => '0',
        ]);

        $this->line('Ability: "'.$ability->title.'" processed');

        $ability = Ability::firstOrNew([
            'name' => 'view_analysis_pages_of',
            'title' => 'View analysis pages of modems',
            'entity_type' => \Modules\ProvBase\Entities\Cmts::class,
            'only_owned' => '0',
        ]);

        $this->line('Ability: "'.$ability->title.'" processed');

        $ability = Ability::firstOrNew([
            'name' => 'download',
            'title' => 'Download settlement runs',
            'entity_type' => \Modules\BillingBase\Entities\SettlementRun::class,
            'only_owned' => '0',
        ]);

        $this->line('Ability: "'.$ability->title.'" processed');

        $this->setVerbosity('normal');

        $this->info('success');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            // array('example', InputArgument::REQUIRED, 'An example argument.'),
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            // array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        ];
    }
}
