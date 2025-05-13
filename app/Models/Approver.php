<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'email',
        'position',
        'email',
        'role',
        'branch_code',
        'signature',
        'branch',
        'employee_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvers()
    {
        return $this->hasMany(CustomApprovers::class);

    }
}