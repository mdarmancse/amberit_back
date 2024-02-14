<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Audit extends Model
{
    use HasFactory;

    public function user(){

        return $this->belongsTo(User::class);
    }

    public function content(){

        return $this->belongsTo(Content::class,'auditable_id');
    }

    public function featured_content(){

        return $this->belongsTo(FeaturedContent::class,'auditable_id');
    }

    public function poll(){

        return $this->belongsTo(Poll::class,'auditable_id');
    }
}
