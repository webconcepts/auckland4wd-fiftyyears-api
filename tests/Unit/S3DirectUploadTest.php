<?php

use App\S3DirectUpload;

class S3DirectUploadTest extends TestCase
{
    /** @test **/
    public function can_get_request_data()
    {
        $upload = app(S3DirectUpload::class);

        $data = $upload->getRequestData();

        $this->assertNotEmpty($data['acl']);
        $this->assertNotEmpty($data['success_action_status']);
        $this->assertNotEmpty($data['policy']);
        $this->assertNotEmpty($data['X-amz-credential']);
        $this->assertNotEmpty($data['X-amz-algorithm']);
        $this->assertNotEmpty($data['X-amz-date']);
        $this->assertNotEmpty($data['X-amz-signature']);
    }

    /** @test **/
    public function can_set_key_value_and_have_it_included_in_the_request_data()
    {
        $upload = app(S3DirectUpload::class);

        $upload->setKey('path/name/filename.jpg');
        $data = $upload->getRequestData();

        $this->assertEquals('path/name/filename.jpg', $upload->getKey());
        $this->assertEquals('path/name/filename.jpg', $data['key']);
    }

    /** @test **/
    public function can_set_content_type_value_and_have_it_included_in_the_request_data()
    {
        $upload = app(S3DirectUpload::class);

        $upload->setContentType('image/jpeg');
        $data = $upload->getRequestData();

        $this->assertEquals('image/jpeg', $data['Content-Type']);
    }

    /** @test **/
    public function can_get_url()
    {
        $upload = app(S3DirectUpload::class);

        $url = $upload->getUrl();
        $parsed = parse_url($url);

        $this->assertNotEmpty($url);
        $this->assertNotFalse(stripos($parsed['host'], 's3'));
        $this->assertNotFalse(stripos($parsed['host'], 'amazonaws.com'));
        $this->assertNotFalse(stripos($parsed['path'], env('AWS_S3_BUCKET')));
    }
}
