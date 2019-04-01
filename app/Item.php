<?php

namespace App;

use Exception;
use App\PhotoStore;
use App\Video\VideoInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use ObfuscatesId;

    const PHOTO_ALBUM = 1;
    const VIDEO = 2;
    const MILESTONE = 3;

    protected $guarded = [];

    protected $dates = ['date'];

    /**
     * @var array $types accepted values for type
     */
    protected static $types = [
        self::PHOTO_ALBUM => 'photoalbum',
        self::VIDEO => 'video',
        self::MILESTONE => 'milestone',
    ];

    /**
     * @var array $videoTypes accepted values for video_type
     */
    protected static $videoTypes = [
        1 => 'youtube',
        2 => 'vimeo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coverPhoto()
    {
        return $this->belongsTo(Photo::class)->whereNull('removed_at');
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
        $this->attributes['title'] = app(ContentEditableInput::class)->sanitise($value);
    }

    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = app(ContentEditableInput::class)->sanitise($value);
    }

    public function setAuthorshipAttribute($value)
    {
        $this->attributes['authorship'] = app(ContentEditableInput::class)->sanitise($value);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = app(ContentEditableInput::class)->sanitise($value, '<p><br><div>');
    }

    public function setVideoTypeAttribute($value)
    {
        $type = self::videoTypes()->search($value);

        $this->attributes['video_type'] = $type ? $type : null;
    }

    public function getVideoTypeAttribute($value)
    {
        return self::videoTypes()->get($value);
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
     * Get the accepted video types
     *
     * @return array value => name
     */
    public static function videoTypes()
    {
        return collect(self::$videoTypes);
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
     * Create a new photo and associate it with this item
     *
     * @param string $originalFilename
     * @param string $type mime type
     * @return Photo
     */
    public function addNewPhoto($originalFilename, $type, $number = null)
    {
        return $this->photos()->create([
            'uploaded_by_id' => Auth::id(),
            'original_filename' => $originalFilename,
            'type' => $type,
            'number' => ($number !== null) ? $number : $this->getNextAvailablePhotoNumber()
        ]);
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

    /**
     * Set the video type and id from this items video url.
     *
     * @return $this
     */
    public function setVideoDetailsFromUrl()
    {
        $info = app(VideoInfo::class, ['url' => $this->video_url]);

        $this->update([
            'video_type' => $info->getType(),
            'video_id' => $info->getId()
        ]);

        if ($url = $info->getImageUrl()) {
            $photo = $this->addNewPhoto($url, 'image/jpeg');

            // upload image to s3
            if (app(PhotoStore::class)->putFileFromURL($photo, $url)) {
                $photo->update(['uploaded' => true]);
                $this->coverPhoto()->associate($photo)->save();
            }
        }

        return $this;
    }

    public function toArray()
    {
        $base = [
            'id' => $this->obfuscatedId(),
            'title' => $this->title,
            'date' => ($this->date) ? $this->date->toDateString() : null, // yyyy-mm-dd
            'approx_day' => $this->approx_day ? (int) $this->approx_day : null,
            'approx_month' => $this->approx_month ? (int) $this->approx_month : null,
            'approx_year' => $this->approx_year ? (int) $this->approx_year : null,
            'description' => $this->description,
        ];

        if ($this->type == self::PHOTO_ALBUM) {
            return $base + [
                'location' => $this->location,
                'authorship' => $this->authorship,
                'cover_photo_id' => $this->obfuscatedId('cover_photo_id'),
                'type' => 'photo-album',
            ];
        } elseif ($this->type == self::VIDEO) {
            return $base + [
                'location' => $this->location,
                'authorship' => $this->authorship,
                'video_url' => $this->video_url,
                'video_type' => $this->video_type,
                'video_id' => $this->video_id,
                'cover_photo_id' => $this->obfuscatedId('cover_photo_id'),
                'type' => 'video',
            ];
        }

        return $base;
    }
}
