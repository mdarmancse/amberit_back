<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmNotification extends Model
{
    use HasFactory;

    protected $fillable = ['notification_title', 'notification_text', 'thumbnail', 'resource_url', 'content_id', 'user_id'];


    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
