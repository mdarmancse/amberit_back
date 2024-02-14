<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name','status'];

    protected $auditInclude = ['name','status'];

}
