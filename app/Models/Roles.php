<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admins;

class Roles extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'description'
    ];

    public function admins()
    {
        return $this->hasMany(Admins::class);
    }

}
