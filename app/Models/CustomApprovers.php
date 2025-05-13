<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomApprovers extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'noted_by', 'approved_by'];
    protected $casts = [
        'noted_by' => 'array',
        'approved_by' => 'array',
    ];

    public function setFormDataAttribute($value)
    {
        $this->attributes['noted_by'] = json_encode($value);
        $this->attributes['approved_by'] = json_encode($value);
    }

    public function requestForms()
     {
         return $this->hasMany(RequestForm::class);
     }

     public function approvers()
     {
         return $this->belongsTo(Approver::class);
     }
     public function user()
     {
         return $this->belongsTo(User::class);
     }

    
}