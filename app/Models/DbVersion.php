<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DbVersion extends Model
{
    use HasFactory;

    protected $fillable = ['api_name', 'api_version', 'db_version', 'created_by', 'updated_by'];

}
