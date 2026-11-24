<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';

    protected $fillable = [
        'id_paket',
        'id_maskapai',
        'nomor_penerbangan',
        'tempat_kumpul',
        'tanggal_kumpul',
        'tanggal_keberangkatan',
        'tanggal_pulang',
        'kuota',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_keberangkatan' => 'date',
            'tanggal_pulang' => 'date',
            'tanggal_kumpul' => 'datetime',
            'kuota' => 'integer',
        ];
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'id_paket');
    }

    public function maskapai(): BelongsTo
    {
        return $this->belongsTo(Maskapai::class, 'id_maskapai');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_jadwal');
    }

    public function scopeTersedia($query)
    {
        return $query->where('status', 'Tersedia')->where('kuota', '>', 0);
    }
}
