<?php

namespace App;

use Jenssegers\Optimus\Optimus;

class OptimusIdObfuscator implements IdObfuscator
{
    protected $optimus;

    public function __construct($prime, $inverse, $random)
    {
        $this->optimus = new Optimus($prime, $inverse, $random);
    }

    public function encode($id)
    {
        return $this->optimus->encode($id);
    }

    public function decode($id)
    {
        return $this->optimus->decode($id);
    }
}
