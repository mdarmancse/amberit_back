<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use URL;


class PollResource extends JsonResource
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
            'question' => $this->question,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'poll_banner' =>  $this->poll_banner,
            'auth_required' => $this->auth_required,
            'status' => $this->status,
            'poll_options' => $this->poll_options,
            'poll_results' => $this->poll_results,
            'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDayDateTimeString(),
            'edit_url' => Auth::user()->can('edit') ? URL::route('poll.edit', $this->id):null,
            'is_admin' => Auth::user()->hasRole('admin'),
            'result_url' => Auth::user()->can('read') ? URL::route('poll.result', $this->id):null,
        ];
    }
}
