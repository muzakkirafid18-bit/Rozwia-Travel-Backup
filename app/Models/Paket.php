<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    use HasFactory;

    protected $table = 'paket';

    protected $fillable = [
        'id_jenis_layanan',
        'nama_paket',
        'kategori',
        'harga_dasar',
        'deskripsi',
        'fasilitas',
        'status',
        'gambar_utama',
    ];

    protected function casts(): array
    {
        return [
            'harga_dasar' => 'decimal:2',
            'fasilitas'   => 'array',
        ];
    }

    /**
     * Harga paket terendah berdasarkan tipe kamar Quad (fallback ke harga_dasar).
     */
    public function quadPrice(): float
    {
        $this->loadMissing('paketTipeKamars.tipeKamar');
        $baseHarga = (float) ($this->harga_dasar ?? 0);

        $quad = $this->paketTipeKamars->first(function ($ptk) {
            return strtolower(trim((string) ($ptk->tipeKamar->nama ?? ''))) === 'quad'
                && (float) $ptk->harga > 0;
        });

        if ($quad) {
            $quadHarga = (float) $quad->harga;
            // kalau harga kamar lebih kecil dari harga_dasar (mis. data lama/surcharge), pakai harga_dasar
            if ($baseHarga > 0 && $quadHarga < $baseHarga) {
                return $baseHarga;
            }
            return $quadHarga;
        }

        $minPrice = $this->paketTipeKamars->where('harga', '>', 0)->min('harga');
        if ($minPrice !== null && (float) $minPrice > 0) {
            $minHarga = (float) $minPrice;
            if ($baseHarga > 0 && $minHarga < $baseHarga) {
                return $baseHarga;
            }
            return $minHarga;
        }

        return $baseHarga;
    }

    public function jenisLayanan(): BelongsTo
    {
        return $this->belongsTo(JenisLayanan::class, 'id_jenis_layanan');
    }

    public function gambarPakets(): HasMany
    {
        return $this->hasMany(GambarPaket::class, 'id_paket');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'id_paket');
    }

    public function paketTipeKamars(): HasMany
    {
        return $this->hasMany(PaketTipeKamar::class, 'id_paket');
    }

    public function rangkaianIbadahs(): HasMany
    {
        return $this->hasMany(RangkaianIbadah::class, 'id_paket')->orderBy('urutan');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
