<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
       
      broadcast(new MessageSent($request->message))->toOthers();


        return response()->json(['status' => 'Message sent!']);
    }
}
