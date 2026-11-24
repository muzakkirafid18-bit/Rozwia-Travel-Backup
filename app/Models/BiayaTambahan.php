<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiayaTambahan extends Model
{
    use HasFactory;

    protected $table = 'biaya_tambahan';

    protected $fillable = [
        'id_jamaah_detail',
        'keterangan',
        'nominal_biaya',
        'status',
        'admin_notes',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'nominal_biaya' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function jamaahDetail(): BelongsTo
    {
        return $this->belongsTo(JamaahDetail::class, 'id_jamaah_detail');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
