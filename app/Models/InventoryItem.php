<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function inventoryItemCategory()
    {
        return $this->belongsTo(InventoryItemCategory::class, 'item_category_id');
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'item_id');
    }

    public function wasteItems()
    {
        return $this->hasMany(WasteControl::class, 'item_id');
    }
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_inventory_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function menu_addons()
    {
        return $this->belongsToMany(Menu::class, 'menu_addon_items')
            ->withPivot(['quantity', 'additional_price'])
            ->withTimestamps();
    }

    /**
     * Relationship with Admin (creator).
     */
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'createby');
    }

    /**
     * Relationship with Admin (updater).
     */
    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updateby');
    }
}
