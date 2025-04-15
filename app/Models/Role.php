<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public function userInfos()
    {
        return $this->hasMany(EmployeeInfo::class, 'role_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
