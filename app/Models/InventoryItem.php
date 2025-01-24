<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'item_id');
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
