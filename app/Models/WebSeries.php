<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebSeries extends Model
{
    use HasFactory;

    protected $table="web_series";

    protected $fillable=['series_name','total_sesson_no','sorting','release_language','is_active'];
}
