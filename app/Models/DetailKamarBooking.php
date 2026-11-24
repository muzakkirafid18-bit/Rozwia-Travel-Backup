<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailKamarBooking extends Model
{
    use HasFactory;

    protected $table = 'detail_kamar_booking';

    protected $fillable = [
        'id_booking',
        'id_paket_tipe_kamar',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking');
    }

    public function paketTipeKamar(): BelongsTo
    {
        return $this->belongsTo(PaketTipeKamar::class, 'id_paket_tipe_kamar');
    }
}
