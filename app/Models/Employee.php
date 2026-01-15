<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nik',
        'name',
        'gender',
        'phone',
        'position'
    ];

    protected $casts = [
        'user_id' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getGenderOptions(): array
    {
        return [
            'Laki-Laki' => 'Laki-Laki',
            'Perempuan' => 'Perempuan'
        ];
    }
}
