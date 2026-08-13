<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
'sent_at',
'answer',
'message',
'user_id',
'session_id'

])]


class Message extends Model
{
    //
    public $timestamps = false;
    public function user(){
    return $this->belongsTo(User::class);
}

}
