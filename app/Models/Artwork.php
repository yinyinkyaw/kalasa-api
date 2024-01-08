<?php

namespace App\Models;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Artwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'artist_id',
        'year',
        'category_id',
        'size',
        'description',
        'price',
        'status',
    ];

    public function artist() {
        return $this->belongsTo(Artist::class, 'artist_id');
    }
}
