<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Post extends Model
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $guarded = [];

    public function getUser()
    {
        return $this->hasOne(User::class, 'id', 'created_by')->select('id', 'name');
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->select('id', 'name');
    }

    public function getSubcategory()
    {
        return $this->hasOne(Subcategory::class, 'id', 'subcategory_id')->select('id', 'subcategory');
    }
}