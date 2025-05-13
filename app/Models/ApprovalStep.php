<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'approver_id',
        'step_order',
        'is_approved',
        'approval_date',
    ];

    public function form()
    {
        return $this->belongsTo(RequestForm::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
