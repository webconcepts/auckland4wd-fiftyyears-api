<?php

use App\Photo;
use Aws\Command;
use Aws\S3\S3Client;
use App\S3PhotoStore;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Aws\S3\Exception\S3Exception;
use App\Exceptions\PhotoStoreException;

class S3PhotoStoreTest extends TestCase
{
    protected function mockS3Client()
    {
        $this->app->instance(S3Client::class, $mock = Mockery::mock(S3Client::class));

        return $mock;
    }

    protected function mockGuzzleGet($url, $mockReturnString)
    {
        $this->app->instance(
            Client::class,
            Mockery::mock(Client::class)
                ->shouldReceive('get')
                ->with($url)
                ->andReturn(Mockery::mock(Response::class)
                    ->shouldReceive('getBody')
                    ->andReturn($mockReturnString)
                    ->getMock())
                ->getMock()
        );
    }

    /** @test **/
    public function can_get_key_for_a_photo()
    {
        $photoStore = app(S3PhotoStore::class);

        $photo = new Photo([
            'id' => 123,
            'item_id' => 321
        ]);

        $this->assertEquals(
            'test/'.$photo->obfuscatedId('item_id').'/'.$photo->obfuscatedId(),
            $photoStore->getKey($photo)
        );
    }

    /** @test **/
    public function can_put_file_to_s3_for_a_photo()
    {
        $photo = new Photo([
            'id' => 123,
            'item_id' => 321
        ]);

        $this->mockGuzzleGet('https://www.photos.com/123', 'test-stream-body');
        $this->mockS3Client()
            ->shouldReceive('putObject')
            ->with([
                'Bucket' => env('AWS_S3_BUCKET'),
                'Key' => 'test/'.$photo->obfuscatedId('item_id').'/'.$photo->obfuscatedId(),
                'Body' => 'test-stream-body'
            ])
            ->andReturn(true);

        $photoStore = app(S3PhotoStore::class);

        $this->assertTrue($photoStore->putFileFromURL($photo, 'https://www.photos.com/123'));
    }

    /** @test **/
    public function put_file_throws_a_photo_store_exception_on_s3_exception()
    {
        $photo = new Photo(['id' => 123, 'item_id' => 321]);

        $this->mockGuzzleGet('https://www.photos.com/123', 'test-stream-body');
        $this->mockS3Client()
            ->shouldReceive('putObject')
            ->andThrow(new S3Exception('Test S3 exception message', new Command('test')));

        $photoStore = app(S3PhotoStore::class);

        try {
            $photoStore->putFileFromURL($photo, 'https://www.photos.com/123');
        } catch (PhotoStoreException $e) {
            $this->assertEquals('Test S3 exception message', $e->getMessage());
            return;
        }

        $this->fail('Expected PhotoStoreException to be thrown');
    }
}
