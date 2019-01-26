<?php

use App\OptimusIdObfuscator;

class OptimusIdObfuscatorTest extends TestCase
{
    /** @test **/
    public function public_ids_are_the_same_for_the_same_private_id()
    {
        $obfuscator = new OptimusIdObfuscator(961472683, 1772474883, 471565496);

        $publicID1 = $obfuscator->encode(24);
        $publicID2 = $obfuscator->encode(24);

        $this->assertEquals($publicID1, $publicID2);
    }

    /** @test **/
    public function public_ids_are_the_different_for_different_private_id()
    {
        $obfuscator = new OptimusIdObfuscator(961472683, 1772474883, 471565496);

        $publicID1 = $obfuscator->encode(24);
        $publicID2 = $obfuscator->encode(3);

        $this->assertNotEquals($publicID1, $publicID2);
    }

    /** @test **/
    public function public_ids_can_be_decoded_to_their_private_id()
    {
        $obfuscator = new OptimusIdObfuscator(961472683, 1772474883, 471565496);

        $publicID = $obfuscator->encode(143);

        $this->assertEquals(143, $obfuscator->decode($publicID));
    }

    /** @test **/
    public function public_ids_are_different_for_different_optimus_prime_inverse_and_random_numbers()
    {
        $obfuscatorA = new OptimusIdObfuscator(961472683, 1772474883, 471565496);
        $obfuscatorB = new OptimusIdObfuscator(1508687911, 838905751, 1148198);

        $publicID1 = $obfuscatorA->encode(72);
        $publicID2 = $obfuscatorB->encode(72);

        $this->assertNotEquals($publicID1, $publicID2);
    }
}
