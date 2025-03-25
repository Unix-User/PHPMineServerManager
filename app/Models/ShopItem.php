<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase;

class ShopItem extends Model
{
    use HasFactory;
    protected $table = 'shop_items';
    protected $fillable = ['name', 'description', 'price', 'item_photo_path', 'link'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}


