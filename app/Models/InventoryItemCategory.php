<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItemCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class, 'item_category_id');
    }
}
