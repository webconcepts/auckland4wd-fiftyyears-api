<?php

use App\Video\OEmbedVideoInfo;
use App\Exceptions\VideoInfoFailedException;

/**
 * @group integration
 */
class OEmbedVideoInfoTest extends TestCase
{
    use VideoInfoContractTests;

    protected function newVideoInfo($url)
    {
        return new OEmbedVideoInfo($url);
    }

    /** @test **/
    public function exception_thrown_when_no_info_was_retrieved_about_url()
    {
        try {
            $info = $this->newVideoInfo('http://www.google.com');
        } catch (VideoInfoFailedException $e) {
            $this->assertInstanceOf(VideoInfoFailedException::class, $e);
            return;
        }

        $this->fail('Expected VideoInfoFailedException to be thrown');
    }
}
