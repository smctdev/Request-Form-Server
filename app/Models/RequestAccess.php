<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;

class RequestAccess extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts()
    {
        return [
            'status' => Status::class,
        ];
    }
}
