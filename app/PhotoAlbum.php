<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PhotoAlbum extends Model
{
    protected $guarded = [];

    protected $dates = ['date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
            'date' => ($this->date) ? $this->date->toDateString() : null, // yyyy-mm-dd
            'location' => $this->location,
            'photographer' => $this->photographer,
            'description' => $this->description,
        ];
    }
}