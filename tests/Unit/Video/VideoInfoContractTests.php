<?php

use App\Exceptions\VideoInfoFailedException;
use App\Exceptions\InvalidVideoTypeException;

trait VideoInfoContractTests
{
    abstract protected function newVideoInfo($url);

    protected function assertImageUrl($actual)
    {
        $parsed = parse_url($actual);
        $this->assertNotEmpty($parsed['host']);
        $this->assertNotEmpty($parsed['path']);

        $this->assertTrue(stripos($actual, '.jpg') || stripos($actual, '.webp'));
    }

    /** @test **/
    public function can_get_type_and_image_url_for_vimeo_video()
    {
        $info = $this->newVideoInfo('http://vimeo.com/304131475');

        $this->assertEquals('vimeo', $info->getType());
        $this->assertImageUrl($info->getImageUrl());
    }

    /** @test **/
    public function can_get_type_and_image_url_for_youtube_url()
    {
        $info = $this->newVideoInfo('https://www.youtube.com/watch?v=WE8gcyr5NMQ');

        $this->assertEquals('youtube', $info->getType());
        $this->assertImageUrl($info->getImageUrl());
    }

    /** @test **/
    public function get_key_for_vimeo_video()
    {
        $info = $this->newVideoInfo('http://vimeo.com/304131475');
        $this->assertEquals('304131475', $info->getId());

        $info = $this->newVideoInfo('https://player.vimeo.com/video/304131475?title=0&byline=0&portrait=0');
        $this->assertEquals('304131475', $info->getId());
    }

    /** @test **/
    public function get_key_for_youtube_video()
    {
        $info = $this->newVideoInfo('https://www.youtube.com/watch?v=WE8gcyr5NMQ');
        $this->assertEquals('WE8gcyr5NMQ', $info->getId());

        $info = $this->newVideoInfo('https://youtube.com/v/WE8gcyr5NMQ');
        $this->assertEquals('WE8gcyr5NMQ', $info->getId());

        $info = $this->newVideoInfo('http://youtu.be/WE8gcyr5NMQ');
        $this->assertEquals('WE8gcyr5NMQ', $info->getId());
    }

    /** @test **/
    // public function exception_thrown_when_no_info_was_retrieved_about_url()
    // {
    //     try {
    //         $info = $this->newVideoInfo('http://www.google.com');
    //     } catch (VideoInfoFailedException $e) {
    //         $this->assertInstanceOf(VideoInfoFailedException::class, $e);
    //         return;
    //     }

    //     $this->fail('Expected VideoInfoFailedException to be thrown');
    // }

    /** @test **/
    public function exception_thrown_when_url_is_of_invalid_video_type()
    {
        try {
            $info = $this->newVideoInfo('https://www.dailymotion.com/video/x73yder');
        } catch (InvalidVideoTypeException $e) {
            $this->assertInstanceOf(InvalidVideoTypeException::class, $e);
            return;
        }

        $this->fail('Expected InvalidVideoTypeException to be thrown');
    }
}
