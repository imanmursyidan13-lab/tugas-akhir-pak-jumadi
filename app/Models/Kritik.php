<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kritik extends Model
{
    use HasFactory;

    protected $table = 'kritiks';
    protected $primaryKey = 'id';
    protected $fillable = ['comment', 'rating', 'film_id', 'user_id'];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Satu komentar bisa punya banyak balasan dari user lain
    public function balasans(): HasMany
    {
        return $this->hasMany(Balasan::class);
    }
}
