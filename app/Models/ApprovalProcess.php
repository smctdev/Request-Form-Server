<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_form_id',
        'custom_approvers_id',
        'level',
        'status',
        'comment',
        'attachment',
        
    ];

    protected $attributes = [
        'status' => 'Pending',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestForm()
    {
        return $this->belongsTo(RequestForm::class);
    }

    public function customApprover()
    {
        return $this->belongsTo(CustomApprovers::class);
    }
    public function previousApprovalProcess()
    {
        return $this->hasOne(ApprovalProcess::class, 'request_form_id', 'request_form_id')
                    ->where('level', '<', $this->level)
                    ->where('status', '!=', 'approved')
                    ->orderBy('level', 'desc');
    }
    
}