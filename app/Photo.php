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

    public function item()
    {
        return $this->belongsTo(Item::class);
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

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = app(ContentEditableInput::class)->sanitise($value);
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
     * Get the next photo in this photo's photo album
     *
     * @return mixed Photo or null
     */
    public function next()
    {
        return self::where('item_id', $this->item_id)
            ->where('uploaded', true)
            ->where('number', '>', $this->number)
            ->orderBy('number')
            ->first();
    }

    /**
     * Get the previous photo in this photo's photo album
     *
     * @return mixed Photo or null
     */
    public function previous()
    {
        return self::where('item_id', $this->item_id)
            ->where('uploaded', true)
            ->where('number', '<', $this->number)
            ->orderBy('number', 'desc')
            ->first();
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
        if ($this->item->isDraft()) {
            $this->update(['removed_at' => $this->freshTimestamp()]);
        }
    }

    public function toArray()
    {
        return [
            'id' => $this->obfuscatedId(),
            'number' => (int) $this->number,
            'uploaded' => $this->isUploaded(),
            'description' => $this->description,
            'likes' => (int) $this->likes,
        ];
    }
}
