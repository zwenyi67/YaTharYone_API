<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function waiter() {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function cashier() {
        return $this->belongsTo(User::class, 'cashier_id');
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
