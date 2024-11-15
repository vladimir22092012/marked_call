<?php

namespace App\Jobs;

use App\Mail\SendBotMail;
use App\Models\Events;
use App\Models\GptPrompt;
use App\Services\Owners;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GptProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public GptPrompt $prompt, public string $title)
    {}

    public function handle(): void
    {
        $prompt = $this->prompt->prompt;
        $prompt .= ' Если слово найдено в реплике менеджера, верни true в формате json, иначе верни false а формате json. Параметр в ответе назови exist.';
        $prompt .= ' Тест:'.$this->title;
        $request = Http::post('http://mcc-15-16.in-fo.ru/webHooks/getGptPP', [
            'textContent' => '',
            'prompt' => $prompt,
        ]);
        $response = json_decode($request->body(), true);
        $response = json_decode(str_replace("<br />\n", '', $response['result']['result']), true);

        if ($response['exist']) {
            Log::info('exist');
            Mail::to($this->prompt->email)->send(new SendBotMail($this->title));
        }
    }

}
