<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumPackage extends Model
{
    use HasFactory;

    protected $fillable = ['package_name', 'package_code', 'package_description', 'package_logo', 'package_logo', 'share_url', 'price', 'price_currency', 'package_duration', 'duration_unit', 'rating', 'package_type_id', 'is_active', 'package_thumbnail', 'pack_expire_date', 'package_type'];

    public function package_type(){
        return $this->hasMany(PackageTypes::class);
    }
}
