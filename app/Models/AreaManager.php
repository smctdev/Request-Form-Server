<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
    ];

    protected $casts = [
        'branch_id' => 'array',
    ];

    public function setFormDataAttribute($value)
    {
        $this->attributes['branch_id'] = json_encode($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}