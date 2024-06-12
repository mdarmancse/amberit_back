<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ContentResource extends JsonResource
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
            'content_name' => $this->content_name,
            'content_description' => base64_decode($this->content_description),
            'content_type' => $this->content_type,
            'content_identity' => $this->content_identity,
            'genre' => $this->genre,
            'quality' => $this->quality,
            'rating' => $this->rating,
            'is_active' => $this->is_active,
            'is_trailer_available' => $this->is_trailer_available,
            'trailer_uri' => $this->trailer_uri,
            'is_premium' => $this->is_premium,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'subcategory_id' => $this->subcategory_id,
            'subcategory_name' => $this->subcategory_name,
            'release_year' => $this->release_year,
            'release_date' => $this->release_date,
            'language' => $this->language,
            'poster' => $this->poster,
            'backdrops_Poster' => $this->backdrops_Poster,
            'home_page_link' => $this->home_page_link,
            'runtime' => $this->runtime,
            'actors' => $this->actors,
            'size' => $this->size,
            'file_location' => $this->file_location,
            'last_air_date' => $this->last_air_date,
            'keywords' => $this->keywords,
            'views' => $this->views,
            'is_tv_series' => $this->is_tv_series,
            'tv_series_id' => $this->tv_series_id,
            'tv_series_name' => $this->tv_series_name,
            'season_number' => $this->season_number,
            'episode_number' => $this->episode_number,
            'episode_identity' => $this->episode_identity,
            'overview' => $this->overview,
            'is_adult' => $this->is_adult,

            'created_at' =>  Carbon::parse($this->created_at)->toDayDateTimeString(),
            'updated_at' =>  Carbon::parse($this->updated_at)->toDayDateTimeString(),

            //  'edit_url' => Auth::user()->can('edit') ? URL::route('contents.edit', $this->id):null
        ];
    }
}
