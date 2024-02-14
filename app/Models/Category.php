<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'category';
    protected $fillable = ['category_name', 'category_logo', 'is_active', 'sort_order','created_by','updated_by'];


    public function subCategories(){

        return $this->hasMany(SubCategory::class);
    }
}
