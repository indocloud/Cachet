<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Subscribers;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * This is the command subscriber class.
 *
 * @author James Brooks <james@alt-three.com>
 * @author Graham Campbell <graham@alt-three.com>
 */
class CommandSubscriber
{
    /**
     * The config repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Create a new command subscriber instance.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     *
     * @return void
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen('command.installing', __CLASS__.'@fire', 5);
        $events->listen('command.updating', __CLASS__.'@fire', 5);
        $events->listen('command.resetting', __CLASS__.'@fire', 5);
    }

    /**
     * Backup the databases.
     *
     * @param \Illuminate\Console\Command $command
     *
     * @return void
     */
    public function backup(Command $command)
    {
        $command->line('Backing up database...');

        try {
            $command->call('db:backup', [
                '--compression'     => 'gzip',
                '--database'        => $this->config->get('database.default'),
                '--destination'     => 'local',
                '--destinationPath' => Carbon::now()->format('Y-m-d H.i.s'),
                '--no-interaction'  => true,
            ]);
        } catch (Exception $e) {
            $command->error($e->getMessage());
            $command->line('Backup skipped!');
        }

        $command->line('Backup completed!');
    }

    /**
     * Clear the cachet settings cache.
     *
     * @param \Illuminate\Console\Command $command
     *
     * @return void
     */
    public function clear(Command $command)
    {
        $command->line('Clearing settings cache...');

        $this->loader->clear();

        $command->line('Settings cache cleared!');
    }
}
