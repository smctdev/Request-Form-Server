<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class, 'branch_code');
    }

    public function approverBranchStaffs()
    {
        return $this->hasMany(AVPFinanceStaff::class, 'branch_id');
    }
}
