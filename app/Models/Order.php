<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function table() {
        return $this->belongsTo(Table::class);
    }

    public function waiter() {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'createby');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updateby');
    }
}
