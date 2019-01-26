<?php

namespace App;

interface IdObfuscator
{
    /**
     * Should take an actual id and return an obfuscated value
     *
     * @param int id actual id
     * @return mixed obfuscated id
     */
    public function encode($id);

    /**
     * Should take an obfuscated id and decode into the actual id value
     *
     * @param mixed id obfuscated value
     * @return int actual id
     */
    public function decode($id);
}
