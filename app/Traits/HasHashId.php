<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Vinkla\Hashids\Facades\Hashids as LaravelHashids;

trait HasHashId
{
    protected function getHashIdConnection()
    {
        return 'main';
    }

    public function initializeHasHashId()
    {
        $this->append('hash_id');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return self::findOrFail(LaravelHashids::connection($this->getHashIdConnection())->decode($value)[0]);
    }

    public function hashId(): Attribute
    {
        return Attribute::make(
            get: fn() => LaravelHashids::connection($this->getHashIdConnection())->encode($this->id)
        );
    }
}