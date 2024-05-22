<?php

namespace App\Models;
use App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shifts extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'entry_time',
        'finish_time',
        'shift_duration',
        'mothly_late_allowance',
    ];

    


    public function users()
    {
        return $this->hasMany(Users::class);
    }

}
