<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
class ChatbotController extends Controller
{
    //
  public function sendMessage(StoreMessageRequest $request): JsonResponse{
    $user=$request->user();
    $valid=$request->validated();
$response=Http::post(config('services.ai-services.url' ).'/chat',
   [ 'message'=>$valid['message'],
    'session_id'=>$valid['session_id']??null,]
);
$response->throw();
$answer=$response->json('answer');
$sessionid=$response->json('session_id');
//after calling fastapi
$src = $response->json('sources');
Message::create([
    "message"=>$valid['message'],
    "user_id"=>$user->id,
    "answer"=>$answer,
    'session_id'=>$sessionid,
    'sent_at'=>now(),


]);


      return response()->json([
        "message"=> "Chatbot response generated successfully.",
            "data" =>[
                'answer'=>$answer,
                'session_id'=>$sessionid,
                "sources"=>$src,
            ]
        ], 201);
        //201-->created
    
  }
  public function retrieveMessages(Request $request): JsonResponse{
     $user=$request->user();
     $allmessages=Message::where('user_id',$user->id)
     ->orderBy('sent_at','asc')
     ->get(
        ['id','message','answer','session_id','sent_at']
     );
     return response()->json(
        [ "message"=> "Chat history retrieved successfully.",

        "data"=>$allmessages,
]

     );
  }
}
