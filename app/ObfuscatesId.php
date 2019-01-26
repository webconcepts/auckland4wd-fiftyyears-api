<?php

namespace App;

/**
 * Trait for models to help with getting obfuscated and actual id values
 */
trait ObfuscatesId
{
    public function obfuscatedId()
    {
        return app(IdObfuscator::class)->encode($this->id);
    }

    public static function actualId($obfuscatedId)
    {
        return app(IdObfuscator::class)->decode($obfuscatedId);
    }
}
