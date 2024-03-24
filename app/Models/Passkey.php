<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Passkey extends Model
{
    use HasFactory;

    // protected $casts = [
    //     'public_key' => 'encrypted:json',
    // ];

    protected $fillable = [
        'public_key',
        'credential_id',
        'name',
    ];

    protected function passkeyable()
    {
        return $this->morphTo();
    }

    public function credentialId(): Attribute {
        return new Attribute(
            get: fn ($value) => base64_decode($value),
            set: fn ($value) => base64_encode($value),
        );
    }
}