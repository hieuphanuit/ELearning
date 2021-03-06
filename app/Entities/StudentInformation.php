<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class StudentInformation extends Model
{
    //
    protected $table = 'student_informations';

    protected $fillable = [
        'id',
        'user_id',
        'phone_number',
        'school',
        'class',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    } 

}
