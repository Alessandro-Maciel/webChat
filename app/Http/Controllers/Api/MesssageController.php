<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\SendMessage;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

class MesssageController extends Controller
{
    public function listMessages(User $user)
    {
        $userLogado = Auth::user()->id;
        $userTo = $user->id;

        $message = Message::where(
            function ($query) use ($userLogado, $userTo) {
                $query->where([
                    'from' => $userLogado,
                    'to' => $userTo
                ]);
            }
        )->orWhere(
            function ($query) use ($userLogado, $userTo) {
                $query->where([
                    'from' => $userTo,
                    'to' => $userLogado
                ]);
            }
        )->orderBy('created_at', 'asc')->get();


        return response()->json([
            'messages' => $message
        ], Response::HTTP_OK);
    }


    public function store(Request $message)
    {
        $newMessage = new Message();
        $newMessage->content = filter_var($message->content, FILTER_SANITIZE_STRIPPED);
        $newMessage->to = $message->to;
        $newMessage->from = Auth::user()->id;
        $newMessage->save();

        Event::dispatch(new SendMessage($newMessage, $message->to));
    }
}
