<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Events;
use App\Models\Shifts;
use App\Models\Departments;

class Users extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'last_name',
        'age',
        'gender',
        'email',
        'address',
        'phone_number',
        'profile_image',
        'qr_image',
        'shift_id',
        'department_id',
        'status'
    ];


    public function events()
    {
        return $this->belongsToMany(Events::class, 'attendances','user_id', 'event_id')->withPivot( 'entry_time', 'finish_time','attendance_date');
    }


    public function shift()
{
    return $this->belongsTo(Shifts::class);
}


public function department()
{
    return $this->belongsTo(Departments::class);
}
}
