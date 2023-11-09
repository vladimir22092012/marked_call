<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function Termwind\render;

class MarkedCallController extends Controller
{
    public function form() {
        return view('marked_calls.form', [
            'owners' => [
                159 => 'farwater'
            ],
        ]);
    }

    public function startMarked(Request $request)
    {
        echo '<pre>';
        print_r($request->toArray());
    }
}
