<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use URL;

class PackageResource extends JsonResource
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
            'package_name' => $this->package_name,
            'package_code' => $this->package_code,
            'package_type' => $this->package_type,
            'package_description' => base64_decode($this->package_description),
            'package_logo' => $this->package_logo,
            'package_thumbnail' => $this->package_thumbnail,
            'share_url' => base64_decode($this->share_url),
            'pack_expire_date' => Carbon::parse($this->pack_expire_date)->toDayDateTimeString(),
            'price' => $this->price,
            'price_currency' => $this->price_currency,
            'package_duration' => $this->package_duration,
            'duration_unit' => $this->duration_unit,
            'rating' => $this->rating,
            'is_active' => $this->is_active,
            'updated_by' => $this->updated_by,
            'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDayDateTimeString(),
            'edit_url' => Auth::user()->can('edit') ? URL::route('packages.edit', $this->id):null,
            'is_admin' => Auth::user()->hasRole('admin'),
        ];
    }
}
