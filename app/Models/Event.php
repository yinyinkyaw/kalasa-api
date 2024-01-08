<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'title', 'image', 'status', 'location', 'description', 'opening_datetime', 'closing_datetime',
    ];
}
