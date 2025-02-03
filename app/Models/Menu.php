<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function addonItems()
    {
        return $this->belongsToMany(InventoryItem::class, 'menu_addon_items')
                    ->withPivot(['quantity','additional_price'])
                    ->withTimestamps();
    }

    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class, 'menu_inventory_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
