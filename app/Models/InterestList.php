<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestList extends Model
{
    use HasFactory;

    protected $table = "interest_list";

    protected $fillable = ['interest_name', 'is_active'];
}
