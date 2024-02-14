<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use URL;
use Carbon\Carbon;

class DBVersionResource extends JsonResource
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
            'api_name' => $this->api_name,
            'api_version' => $this->api_version,
            'db_version' => $this->db_version,
            'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDayDateTimeString(),
            'edit_url' => Auth::user()->can('edit') ? URL::route('dbversion.edit', $this->id):null,
            'is_admin' => Auth::user()->hasRole('admin')
        ];
    }
}
