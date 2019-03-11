<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use ObfuscatesId;

    const PHOTO_ALBUM = 1;

    protected $guarded = [];

    protected $dates = ['date'];

    /**
     * @var array $types accepted values for type
     */
    protected static $types = [
        self::PHOTO_ALBUM => 'photoalbum'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class)->whereNull('removed_at');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->whereNull('removed_at');
    }

    public function scopeDraft($query)
    {
        return $query->whereNull('published_at')->whereNull('removed_at');
    }

    public function scopeRemoved($query)
    {
        return $query->whereNotNull('removed_at');
    }

    public function scopePhotoAlbum($query)
    {
        return $query->where('type', self::PHOTO_ALBUM);
    }

    public function setTypeAttribute($key)
    {
        if (!self::types()->has($key)) {
            throw new Exception('Invalid type value for Item');
        }

        $this->attributes['type'] = $key;
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = strip_tags($value);
    }

    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = strip_tags($value);
    }

    public function setAuthorshipAttribute($value)
    {
        $this->attributes['authorship'] = strip_tags($value);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = strip_tags($value, '<p><br>');
    }

    /**
     * Get the accepted types
     *
     * @return array value => name
     */
    public static function types()
    {
        return collect(self::$types);
    }

    /**
     * @return bool true if published
     */
    public function isPublished()
    {
        return $this->published_at !== null;
    }

    /**
     * @return bool true if draft
     */
    public function isDraft()
    {
        return !$this->isPublished() && !$this->isRemoved();
    }

    /**
     * @return bool true if removed
     */
    public function isRemoved()
    {
        return $this->removed_at !== null;
    }

    /**
     * Publish this item. Sets published_at date and persists to db
     */
    public function publish()
    {
        if ($this->isDraft()) {
            $this->update(['published_at' => $this->freshTimestamp()]);
        }
    }

    /**
     * Unpublish this item, return to draft.
     * Sets published_at date to null and persists to db
     */
    public function unpublish()
    {
        $this->update(['published_at' => null]);
    }

    /**
     * Remove this item. Sets removed_at date and persists to db
     */
    public function remove()
    {
        if ($this->isDraft()) {
            $this->update(['removed_at' => $this->freshTimestamp()]);
        }
    }

    /**
     * Get the next highest number that could be used for a photo in this
     * album (the highest number in use, plus 1).
     *
     * @return int
     */
    public function getNextAvailablePhotoNumber()
    {
        return $this->photos()->max('number') + 1;
    }

    /**
     * Set the value of this items date, from the approx_day, approx_month
     * and approx_year values.
     *
     * @return $this
     */
    public function setDateFromApproximateDate()
    {
        if ($this->approx_year) {
            $this->date = sprintf(
                '%s-%s-%s',
                $this->approx_year,
                $this->approx_month ? $this->approx_month : '01',
                $this->approx_day ? $this->approx_day : '01'
            );
        }

        return $this;
    }

    public function toArray()
    {
        return [
            'id' => $this->obfuscatedId(),
            'title' => $this->title,
            'date' => ($this->date) ? $this->date->toDateString() : null, // yyyy-mm-dd
            'approx_day' => $this->approx_day ? (int) $this->approx_day : null,
            'approx_month' => $this->approx_month ? (int) $this->approx_month : null,
            'approx_year' => $this->approx_year ? (int) $this->approx_year : null,
            'location' => $this->location,
            'authorship' => $this->authorship,
            'description' => $this->description,
        ];
    }
}
