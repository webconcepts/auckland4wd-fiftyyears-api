<?php

namespace App;

use EddTurtle\DirectUpload\Signature;

class S3DirectUpload
{
    /**
     * @var EddTurtle\DirectUpload\Signature $upload
     */
    protected $upload;

    /**
     * @var string $key path and filename for file on S3
     */
    protected $key;

    public function __construct($accessKey, $secret, $bucket, $region)
    {
        $this->upload = new Signature($accessKey, $secret, $bucket, $region);
    }

    /**
     * Set the key, the path and filename for the file on S3
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the allowed content type
     *
     * @param string $type
     * @return $this
     */
    public function setContentType($type)
    {
        $this->upload->setOptions(['content_type' => $type]);

        return $this;
    }

    /**
     * Build the url for the upload request.
     *
     * @return string the s3 bucket's url.
     */
    public function getUrl()
    {
        return $this->upload->getFormUrl();
    }

    /**
     * Get an AWS Signature V4 generated.
     *
     * @return string the aws v4 signature.
     */
    public function getSignature()
    {
        return $this->upload->getSignature();
    }

    /**
     * Get the key where the file should be uploaded to
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Generate the necessary data to be included in the upload request.
     * Includes signature in X-amz-signature.
     *
     * @param bool $addKey whether to add the 'key' input (filename)
     * @return array of the form inputs.
     */
    public function getRequestData($addKey = true)
    {
        $data = $this->upload->getFormInputs(false);

        if ($addKey && $this->getKey()) {
            $data['key'] = $this->getKey();
        }

        return $data;
    }
}
