<?php

namespace App;

/**
 * Trait for models to help with getting obfuscated and actual id values
 */
trait ObfuscatesId
{
    public function obfuscatedId($field = 'id')
    {
        return app(IdObfuscator::class)->encode($this->$field);
    }

    public static function actualId($obfuscatedId)
    {
        return app(IdObfuscator::class)->decode($obfuscatedId);
    }
}
