<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AVPFinanceStaff extends Model
{
    use HasFactory;

    protected $table = 'a_v_p_finance_staff';
    protected $fillable = [
        'user_id',
        'staff_id',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'json',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with the staff
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
