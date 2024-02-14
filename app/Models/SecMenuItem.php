<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecMenuItem extends Model
{
    use HasFactory;
    protected $primaryKey = 'menu_id';
    protected $fillable = [
        'menu_id',
        'menu_title',
        'module',
        'parent_menu',
        'createby',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(SecRolePermission::class, 'menu_id');
    }
}
