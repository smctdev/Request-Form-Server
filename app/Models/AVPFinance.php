<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AVPFinance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'staff_id',
    ];

    protected $casts = [
        'staff_id' => 'array',
    ];
}
