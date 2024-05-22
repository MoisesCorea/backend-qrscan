<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abilities extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    

    public function roles()
{
    return $this->belongsToMany(Roles::class, 'rol_abilities', 'ability_id', 'rol_id');
}
}
