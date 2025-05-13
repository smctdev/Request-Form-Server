<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [

        'request_form_id',

        'file_name',

        'file_path',

    ];

    public function requestForm()
    {
        return $this->belongsTo(RequestForm::class);
    }

}