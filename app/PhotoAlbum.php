<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhotoAlbum extends Model
{
    use ObfuscatesId;

    protected $guarded = [];

    protected $dates = ['date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
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
     * Publish this photo album. Sets published_at date and persists to db
     */
    public function publish()
    {
        if ($this->isDraft()) {
            $this->update(['published_at' => $this->freshTimestamp()]);
        }
    }

    /**
     * Unpublish this photo album, return to draft.
     * Sets published_at date to null and persists to db
     */
    public function unpublish()
    {
        $this->update(['published_at' => null]);
    }

    /**
     * Remove this photo album. Sets removed_at date and persists to db
     */
    public function remove()
    {
        if ($this->isDraft()) {
            $this->update(['removed_at' => $this->freshTimestamp()]);
        }
    }

    public function toArray()
    {
        return [
            'id' => $this->obfuscatedId(),
            'title' => $this->title,
            'date' => ($this->date) ? $this->date->toDateString() : null, // yyyy-mm-dd
            'location' => $this->location,
            'photographer' => $this->photographer,
            'description' => $this->description,
        ];
    }
}
