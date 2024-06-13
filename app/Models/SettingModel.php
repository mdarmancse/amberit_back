<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingModel extends Model
{
    use HasFactory;

    protected $table='terms_and_conditions';

    protected $fillable=[
        'id',
        'device_type',
        'terms_and_conditions_white',
        'terms_and_conditions_black',
        'privacy_policy_white',
        'privacy_policy_black',
        'is_active'

    ];
}
