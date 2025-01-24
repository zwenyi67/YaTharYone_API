<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
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

    /**
     * Relationship with PurchaseDetail.
     */
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id');
    }
}
