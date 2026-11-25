<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';

    protected $fillable = [
        'nama_perusahaan',
        'email_perusahaan',
        'telepon_perusahaan',
        'alamat_perusahaan',
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'no_izin',
        'status',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'id_rekening');
    }
}
