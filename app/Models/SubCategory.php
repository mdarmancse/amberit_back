<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $table = 'category_sub';
    protected $fillable = ['sub_category_name', 'category_id', 'sort_order', 'is_active', 'created_by', 'updated_by'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
