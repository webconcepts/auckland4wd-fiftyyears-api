<?php

namespace App;

use App\Events\PhotoSaved;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use ObfuscatesId;

    protected $guarded = [];

    protected $dispatchesEvents = [
        'saved' => PhotoSaved::class,
    ];

    /**
     * @var array $types accepted values for type
     */
    protected static $types = [
        1 => 'image/jpeg'
    ];

    public function photoAlbum()
    {
        return $this->belongsTo(PhotoAlbum::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUploaded($query)
    {
        return $query->where('uploaded', true);
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
     * @return bool true if removed
     */
    public function isRemoved()
    {
        return $this->removed_at !== null;
    }

    /**
     * Remove this photo. Sets removed_at date and persists to db
     */
    public function remove()
    {
        if ($this->photoAlbum->isDraft()) {
            $this->update(['removed_at' => $this->freshTimestamp()]);
        }
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
            'number' => (int) $this->number,
            'uploaded' => $this->isUploaded(),
            'description' => $this->description,
        ];
    }
}
