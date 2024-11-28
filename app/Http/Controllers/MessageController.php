<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Events\MessageEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageResource;

class MessageController extends Controller
{    
    public function sendMessage(MessageRequest $request, $user_id)
    {
        $data = $request->validated();

        $message = Auth::user()->messages()->create([
            'content' => $data['content'],
            'receiver_id' => $user_id
        ]);

        broadcast(new MessageEvent($message))->toOthers();

        return new MessageResource($message);
    }

    public function getMessage($user_id)
    {
        $messages = Message::query()
        ->where(function ($query) use ($user_id) {
            $query->where('user_id', Auth::user()->id)->where('receiver_id', $user_id);
        })
        ->orWhere(function ($query) use ($user_id) {
            $query->where('user_id', $user_id)->where('receiver_id', Auth::user()->id);
        })->get();

        return MessageResource::collection($messages);
    }
}
