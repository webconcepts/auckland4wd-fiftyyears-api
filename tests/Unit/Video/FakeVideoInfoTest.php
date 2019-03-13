<?php

use App\Video\FakeVideoInfo;

class FakeVideoInfoTest extends TestCase
{
    use VideoInfoContractTests;

    protected function newVideoInfo($url)
    {
        return new FakeVideoInfo($url);
    }
}
