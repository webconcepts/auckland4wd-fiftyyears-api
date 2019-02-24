<?php

namespace App;

use App\PhotoAlbum;
use Illuminate\Support\Carbon;
use App\Mail\VerificationEmail;
use App\VerificationCodeGenerator;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, ObfuscatesId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];

    protected $dates = ['verification_expires_at'];

    public function photoAlbums()
    {
        return $this->hasMany(PhotoAlbum::class);
    }

    public function draftPhotoAlbums()
    {
        return $this->hasMany(PhotoAlbum::class)->draft();
    }

    public function publishedPhotoAlbums()
    {
        return $this->hasMany(PhotoAlbum::class)->published();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->obfuscatedId();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Find a user by a given email, or create a new user for that email
     *
     * @param string $email
     * @param string $name
     * @return App\User
     */
    public static function firstOrCreateByEmail($email, $name = null)
    {
        return self::firstOrCreate(
            ['email' => $email],
            ['name' => $name]
        );
    }

    /**
     * Is this user an editor
     *
     * @return bool
     */
    public function isEditor()
    {
        return (bool) $this->editor;
    }

    /**
     * Create a verification code for this user, and email them a verifcation
     * URL using that code
     */
    public function verify()
    {
        $this->verification_code = app(VerificationCodeGenerator::class)->generate();
        $this->verification_expires_at = Carbon::parse('+15 minutes');
        $this->save();

        Mail::to($this->email)->send(new VerificationEmail($this));
    }

    /**
     * Clear the verification code when this user has been successfully verified
     */
    public function verified()
    {
        $this->verification_code = null;
        $this->verification_expires_at = null;
        $this->save();
    }

    public function toArray()
    {
        return [
            'id' => $this->obfuscatedId(),
            'email' => $this->email,
            'name' => $this->name,
        ];
    }
}
