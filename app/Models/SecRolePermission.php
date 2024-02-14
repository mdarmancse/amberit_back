<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecRolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'menu_id',
        'read',
        'create',
        'edit',
        'delete',
        'createby',
    ];

// In the SecRolePermission model

    public function menu()
    {
        return $this->belongsTo(SecMenuItem::class, 'menu_id');
    }

    // Define the relationship with the Role model
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
