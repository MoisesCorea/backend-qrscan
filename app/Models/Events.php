<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'daily_attendance',
        'change_attendance',
        'description'
    ];

        public function users()
        {
            return $this->belongsToMany(Users::class, 'attendances','event_id', 'user_id')->withPivot('entry_time', 'finish_time','attendance_date');
        }
    
}
