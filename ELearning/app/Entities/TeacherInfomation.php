<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class TeacherInfomation extends Model
{
    //
    protected $table = 'teacher_infomations';

    protected $fillable = [
        'id',
        'user_id',
        'phone_number',
        'address',
        'experience',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    } 

}
