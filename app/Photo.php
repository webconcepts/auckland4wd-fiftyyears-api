<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use ObfuscatesId;

    protected $guarded = [];

    /**
     * @var array $types accepted values for type
     */
    protected static $types = [
        1 => 'image/jpeg'
    ];

    public function album()
    {
        return $this->belongsTo(PhotoAlbum::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeAttribute($value)
    {
        return self::types()->get($value);
    }

    public function setTypeAttribute($value)
    {
        $type = self::types()->search($value);

        $this->attributes['type'] = $type ? $type : null;
    }

    /**
     * Get the accepted types (mime types)
     *
     * @return array value => name
     */
    public static function types()
    {
        return collect(self::$types);
    }

    /**
     * Has this photo been confirmed as uploaded to S3?
     *
     * @return bool
     */
    public function isUploaded()
    {
        return (bool) $this->uploaded;
    }

    /**
     * Get the S3 key (path and filename) for this photo
     *
     * @return string
     */
    public function s3Key()
    {
        return $this->obfuscatedId('photo_album_id').'/'.$this->obfuscatedId();
    }

    public function toArray()
    {
        return [
            'id' => $this->obfuscatedId(),
            'number' => $this->number,
            'uploaded' => $this->isUploaded()
        ];
    }
}
