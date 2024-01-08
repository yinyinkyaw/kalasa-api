<?php

namespace App\Models;

use App\Models\Artwork;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'profile_image',
        'description',
        'status',
    ];

}
