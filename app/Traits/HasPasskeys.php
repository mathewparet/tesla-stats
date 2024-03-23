<?php
namespace App\Traits;

use App\Models\Passkey;

trait HasPasskeys
{
    public function passkeys()
    {
        return $this->morphMany(Passkey::class, "passkeyable");
    }
}