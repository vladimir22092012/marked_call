<?php

namespace App\Console\Commands\Gpt;

use App\Events\LayoutNotifyEvent;
use App\Jobs\GptProcessJob;
use App\Mail\SendBotMail;
use App\Models\GptPrompt;
use App\Models\User;
use App\Services\Owners;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class StartGpt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gpt:start';

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
        $from = strtotime(now()->subMinutes(30)->format('Y-m-d H:m'));
        $to = strtotime(now()->format('Y-m-d H:m'));
        $i = 1;
        foreach (GptPrompt::all() as $prompt) {
            $items = Owners::getCalls($prompt->owner, [$from, $to]);
            foreach ($items as $item) {
                GptProcessJob::dispatch($prompt, $item->title, $from, $to)->onQueue('gpt_'.$i);
                $i++;
                if ($i >= 6) {
                    $i = 1;
                }
            }
        }
    }
}
