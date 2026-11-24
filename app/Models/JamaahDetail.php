<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JamaahDetail extends Model
{
    use HasFactory;

    protected $table = 'jamaah_details';

    protected $fillable = [
        'id_booking',
        'nama_lengkap',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'email',
        'nomor_telepon',
        'passport_number',
        'status_kemenag',
        'status_visa',
        'nomor_visa',
        'tanggal_terbit_visa',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir'       => 'date:Y-m-d',
            'tanggal_terbit_visa' => 'date:Y-m-d',
        ];
    }

    /**
     * Samakan label status lama (mis. Dalam Proses / Issued) ke status standar UI.
     */
    public static function normalizeStatus(?string $status): string
    {
        $val = trim((string) ($status ?? ''));
        return match (strtolower($val)) {
            'dalam proses', 'on process', 'proses' => 'Diproses',
            'issued', 'terbit' => 'Terbit',
            'ditolak' => 'Ditolak',
            'belum diajukan', '' => 'Belum Diajukan',
            default => $val,
        };
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking');
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'jamaah_detail_id');
    }

    public function biayaTambahans(): HasMany
    {
        return $this->hasMany(BiayaTambahan::class, 'id_jamaah_detail');
    }
}
