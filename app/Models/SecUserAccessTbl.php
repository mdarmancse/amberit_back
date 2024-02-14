<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecUserAccessTbl extends Model
{
    use HasFactory;

    protected $fillable = [
        'fk_role_id',
        'fk_user_id',
    ];


    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'fk_role_id');
    }
    public function permissions()
    {
        return $this->hasMany(SecRolePermission::class, 'role_id');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(SecRolePermission::class, 'role_id')->with('menu');
    }
}
