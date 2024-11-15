<?php

namespace App\Http\Controllers;

use App\Models\GptPrompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GptController extends Controller
{

    public function settings(Request $request): \Inertia\Response
    {
        return Inertia::render('Gpt/Settings', [
            'gpt_prompt' => $request->user()->gpt_prompt->prompt,
            'email' => $request->user()->gpt_prompt->email
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        GptPrompt::where('user_id', $request->get('user_id'))->update([
            'prompt' => $request->get('gpt_prompt'),
            'email' => $request->get('email')
        ]);
        return response()->json(['status' => 'ok']);
    }

}
