<?php

namespace App;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Client as GuzzleClient;
use App\Exceptions\PhotoStoreException;

class S3PhotoStore implements PhotoStore
{
    /**
     * @var S3Client $client
     */
    protected $client;

    /**
     * @var GuzzleClient $guzzleClient
     */
    protected $guzzleClient;

    protected $bucket;

    protected $keyPrefix;

    public function __construct(S3Client $client, GuzzleClient $guzzleClient, $bucket, $keyPrefix)
    {
        $this->client = $client;
        $this->guzzleClient = $guzzleClient;
        $this->bucket = $bucket;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * Generate the key for the file for a given Photo on S3
     *
     * @param Photo $photo
     * @return string
     */
    public function getKey(Photo $photo)
    {
        return $this->keyPrefix.'/'.$photo->obfuscatedId('item_id').'/'.$photo->obfuscatedId();
    }

    /**
     * Upload a file to S3 for a Photo, from a given URL
     *
     * @param Photo $photo
     * @param string $url
     *
     * @throws PhotoStoreException
     * @return true on success
     */
    public function putFileFromURL(Photo $photo, $url)
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getKey($photo),
                'Body' => $this->guzzleClient->get($url)->getBody(),
            ]);
        } catch (S3Exception $e) {
            throw new PhotoStoreException($e->getMessage());
        }

        return true;
    }
}
