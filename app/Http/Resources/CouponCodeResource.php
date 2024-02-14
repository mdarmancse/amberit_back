<?php

namespace App\Http\Resources;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use URL;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'coupon_code' => $this->coupon_code,
            'discount_amount' => $this->discount_amount,
            'package_short_code' => $this->package_short_code,
            'expiration_date' => Carbon::parse($this->expiration_date)->toDayDateTimeString(),
            'is_active' => $this->is_active,
            'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDayDateTimeString(),
            'edit_url' => Auth::user()->can('edit') ? URL::route('couponcode.edit', $this->id):null,
            'is_admin' => Auth::user()->hasRole('admin'),
        ];
    }
}
