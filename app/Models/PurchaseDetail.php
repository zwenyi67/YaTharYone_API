<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Relationship with InventoryItem.
     */
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'createby');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updateby');
    }
}
