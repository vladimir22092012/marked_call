<?php

namespace App\Console\Commands;

use App\Events\LayoutNotifyEvent;
use App\Mail\SendBotMail;
use App\Models\GptPrompt;
use App\Models\User;
use App\Services\Owners;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

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
    }
}
