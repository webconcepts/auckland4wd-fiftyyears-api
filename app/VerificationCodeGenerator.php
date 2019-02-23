<?php

namespace App;

interface VerificationCodeGenerator
{
    /**
     * @return string
     */
    public function generate();
}
