<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApproverChecker extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }
}
