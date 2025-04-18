<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'is_available',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
