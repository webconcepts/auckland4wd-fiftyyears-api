<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhotoAlbum extends Model
{
    protected $guarded = [];

    protected $dates = ['date'];

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * @return bool true if published
     */
    public function isPublished()
    {
        return $this->published_at !== null;
    }

    /**
     * Publish this photo album. Sets published_at date and persists to db
     */
    public function publish()
    {
        $this->update(['published_at' => $this->freshTimestamp()]);
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'date' => $this->date->toDateString(), // yyyy-mm-dd
            'location' => $this->location,
            'photographer' => $this->photographer,
            'description' => $this->description,
        ];
    }
}