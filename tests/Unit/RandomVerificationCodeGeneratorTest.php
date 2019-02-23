<?php

use App\RandomVerificationCodeGenerator;

class RandomVerificationCodeGeneratorTest extends TestCase
{
    /** @test **/
    public function codes_only_contain_alphanumeric_chars()
    {
        $generator = new RandomVerificationCodeGenerator();

        $code = $generator->generate();

        $this->assertRegExp('/^[a-zA-Z0-9]+$/', $code);
    }

    /** @test **/
    public function code_are_16_chars_in_length()
    {
        $generator = new RandomVerificationCodeGenerator();

        $code = $generator->generate();

        $this->assertEquals(16, strlen($code));
    }

    /** @test **/
    public function each_code_is_different()
    {
        $generator = new RandomVerificationCodeGenerator();

        $codeA = $generator->generate();
        $codeB = $generator->generate();
        $codeC = $generator->generate();
        $codeD = $generator->generate();

        $codes = [$codeA, $codeB, $codeC, $codeD];

        $this->assertEquals(4, count(array_unique($codes)));
    }
}
