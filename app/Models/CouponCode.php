<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponCode extends Model
{
    use HasFactory;
    protected $fillable = ['package_short_code', 'coupon_code', 'discount_amount', 'expiration_date', 'is_active', 'package_id'];
}
