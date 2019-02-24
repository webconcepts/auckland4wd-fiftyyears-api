<?php

use App\User;
use App\Photo;
use App\PhotoAlbum;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AddDraftPhotoAlbumPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_create_a_new_photo_record_and_get_s3_upload_request_data()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg',
            'number' => 1,
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'id',
                'number',
                'uploaded',
            ],
            'upload' => [
                'url',
                'data' => [
                    'Content-Type',
                    'acl',
                    'success_action_status',
                    'policy',
                    'X-amz-credential',
                    'X-amz-algorithm',
                    'X-amz-date',
                    'X-amz-signature',
                ],
            ],
        ]);

        tap(Photo::first(), function ($photo) use ($album) {
            $this->assertEquals($album->id, $photo->photo_album_id);
            $this->assertEquals($album->user_id, $photo->uploadedBy->id);
            $this->assertEquals('photo123.jpg', $photo->original_filename);
            $this->assertEquals('image/jpeg', $photo->type);
            $this->assertEquals(1, $photo->number);
            $this->assertEquals(false, $photo->isUploaded());

            $this->assertEquals('image/jpeg', $this->responseData('upload.data.Content-Type'));
            $this->assertEquals(
                $album->obfuscatedId().'/'.$photo->obfuscatedId(),
                $this->responseData('upload.data.key')
            );
        });
    }

    /** @test **/
    public function cannot_create_a_photo_record_for_a_published_album()
    {
        $album = factory(PhotoAlbum::class)->state('published')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg',
            'number' => 1,
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_create_a_photo_record_for_somebody_elses_album()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();

        Auth::login(factory(User::class)->create());

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg',
            'number' => 1,
        ]);

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_create_a_photo_record_for_somebody_elses_album_when_an_editor()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();

        Auth::login(factory(User::class)->state('editor')->create());

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg',
            'number' => 1,
        ]);

        $this->seeStatusCode(201);
    }

    /** @test **/
    public function filename_is_required()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'type' => 'image/jpeg',
            'number' => 1,
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('filename');
    }

    /** @test **/
    public function type_is_required()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'number' => 1,
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('type');
    }

    /** @test **/
    public function type_must_be_an_accepted_type()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'text/plain',
            'number' => 1,
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('type');
    }

    /** @test **/
    public function number_is_required()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg',
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('number');
    }

    /** @test **/
    public function number_must_be_an_integer()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg',
            'number' => 'A',
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('number');
    }

    /** @test **/
    public function number_must_be_unique_within_album()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();
        factory(Photo::class)->create(['number' => 12, 'photo_album_id' => $album->id]);
        factory(Photo::class)->create(['number' => 24, 'photo_album_id' => $album->id]);

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg',
            'number' => 12,
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('number');
    }
}
