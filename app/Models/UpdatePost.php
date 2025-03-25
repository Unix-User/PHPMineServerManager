<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Update;

class UpdatePost extends Model
{
    use HasFactory;
    protected $table = 'update_posts';
    protected $fillable = ['title', 'content', 'published_at', 'author_id', 'category_id'];

    public function updates()
    {
        return $this->hasMany(Update::class);
    }
}


