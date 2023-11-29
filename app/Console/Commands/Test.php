<?php

namespace App\Console\Commands;

use App\Events\LayoutNotifyEvent;
use App\Models\User;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        sleep(2);
        LayoutNotifyEvent::dispatch('marked_call.end', User::query()->first()->id, '<p><b>Тестовое сообщение</b></p>');
    }
}
