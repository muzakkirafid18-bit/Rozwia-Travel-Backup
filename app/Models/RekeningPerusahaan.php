<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekeningPerusahaan extends Model
{
    use HasFactory;

    protected $table = 'rekening_perusahaan';

    protected $fillable = [
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'nama_perusahaan',
        'alamat_perusahaan',
        'status',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'id_rekening');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
