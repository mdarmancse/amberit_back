<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use URL;
use Carbon\Carbon;

class NotificationResource extends JsonResource
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
            'notification_title' => $this->notification_title,
            'notification_text' => $this->notification_text,
            'thumbnail' => $this->thumbnail,
            'resource_url' => $this->resource_url,
            'content_id' => $this->content_id,
            'user' => $this->user,
            'created_by' => $this->user_id,
            'updated_by' => $this->updated_by,
            'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDayDateTimeString(),
            'edit_url' => Auth::user()->can('edit') ? URL::route('category.edit', $this->id):null,
            'is_admin' => Auth::user()->hasRole('admin'),
        ];
    }
}
