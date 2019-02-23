<?php

namespace App;

use Illuminate\Support\Str;

class RandomVerificationCodeGenerator implements VerificationCodeGenerator
{
    /**
     * @return string random alphanumeric code, 16 chars in length
     */
    public function generate()
    {
        return Str::random(16);
    }
}
