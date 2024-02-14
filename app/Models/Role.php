<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug'
    ];

    public function permissions()
    {
        return $this->hasMany(SecRolePermission::class, 'role_id');
    }
public function users() {

   return $this->belongsToMany(User::class,'users_roles');

}

}
