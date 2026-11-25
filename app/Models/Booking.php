<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'id_pengguna',
        'id_jadwal',
        'kode_booking',
        'nama_rombongan',
        'jumlah_jamaah',
        'total_harga',
        'status_booking',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'total_harga' => 'decimal:2',
            'jumlah_jamaah' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'id_jadwal');
    }

    public function detailKamarBookings(): HasMany
    {
        return $this->hasMany(DetailKamarBooking::class, 'id_booking');
    }

    public function jamaahDetails(): HasMany
    {
        return $this->hasMany(JamaahDetail::class, 'id_booking');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'id_booking');
    }
}
