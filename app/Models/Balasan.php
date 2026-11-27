<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balasan extends Model
{
    use HasFactory;

    protected $table = 'balasans';
    protected $fillable = ['comment', 'kritik_id', 'user_id'];

    public function kritik(): BelongsTo
    {
        return $this->belongsTo(Kritik::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
