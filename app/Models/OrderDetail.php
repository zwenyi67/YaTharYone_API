<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function menu() {
        return $this->belongsTo(Menu::class);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'createby');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updateby');
    }
}
