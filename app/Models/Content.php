<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Content extends Model
{
    use HasFactory;
//    use \OwenIt\Auditing\Auditable;

    protected $table="content_draft";

    protected $fillable = [
        'content_name',
        'content_description',
        'content_type',
        'content_identity',
        'is_trailer_available',
        'trailer_uri',
        'quality',
        'rating',
        'genre',
        'category_id',
        'category_name',
        'subcategory_id',
        'subcategory_name',
        'release_year',
        'release_date',
        'language',
        'backdrops_Poster',
        'poster',
        'home_page_link',
        'runtime',
        'actors',
        'size',
        'file_location',
        'last_air_date',
        'keywords',
        'is_tv_series',
        'tv_series_name',
        'season_number',
        'episode_number',
        'episode_identity',
        'overview',
        'is_active',
        'is_adult',
        'is_premium',
        'created_by',
        'updated_by'
            ];

    /**
     * Attributes to include in the Audit.
     *
     * @var array
     */

    public function tags(){

        return $this->belongsToMany(Tag::class);
    }
    public function interest(){

        return $this->belongsToMany(Interest::class);
    }
}
