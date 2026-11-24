<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'id_booking',
        'id_rekening',
        'invoice_number',
        'amount',
        'batas_pembayaran',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'batas_pembayaran' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(RekeningPerusahaan::class, 'id_rekening');
    }

    public function riwayatPembayarans(): HasMany
    {
        return $this->hasMany(RiwayatPembayaran::class, 'id_invoice');
    }
}
