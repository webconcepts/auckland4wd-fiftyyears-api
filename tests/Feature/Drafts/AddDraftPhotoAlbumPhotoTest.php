<?php

use App\Item;
use App\User;
use App\Photo;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AddDraftPhotoAlbumPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_create_a_new_photo_record_and_get_s3_upload_request_data()
    { $this->withoutExceptionHandling();
        $album = factory(Item::class)->state('album', 'draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'number' => 12,
            'type' => 'image/jpeg'
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
            $this->seeJson(['id' => $photo->obfuscatedId()]);

            $this->assertEquals($album->id, $photo->item_id);
            $this->assertEquals($album->user_id, $photo->uploadedBy->id);
            $this->assertEquals('photo123.jpg', $photo->original_filename);
            $this->assertEquals('image/jpeg', $photo->type);
            $this->assertEquals(12, $photo->number);
            $this->assertEquals(false, $photo->isUploaded());

            $this->assertEquals('image/jpeg', $this->responseData('upload.data.Content-Type'));
            $this->assertEquals(
                'test/'.$album->obfuscatedId().'/'.$photo->obfuscatedId(),
                $this->responseData('upload.data.key')
            );
        });
    }

    /** @test **/
    public function cannot_create_a_photo_record_for_a_published_album()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $this->assertTrue($album->isPublished());

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg'
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_create_a_photo_record_for_somebody_elses_album()
    {
        $album = factory(Item::class)->state('album', 'draft')->create();

        Auth::login(factory(User::class)->create());

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg'
        ]);

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_create_a_photo_record_for_somebody_elses_album_when_an_editor()
    {
        $album = factory(Item::class)->state('album', 'draft')->create();

        Auth::login(factory(User::class)->state('editor')->create());

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg'
        ]);

        $this->seeStatusCode(201);
    }

    /** @test **/
    public function filename_is_required()
    {
        $album = factory(Item::class)->state('album', 'draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'type' => 'image/jpeg'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('filename');
    }

    /** @test **/
    public function type_is_required()
    {
        $album = factory(Item::class)->state('album', 'draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('type');
    }

    /** @test **/
    public function type_must_be_an_accepted_type()
    {
        $album = factory(Item::class)->state('album', 'draft')->create();

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'text/plain'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('type');
    }

    /** @test **/
    public function number_is_incremented_when_given_number_already_exists()
    {
        $album = factory(Item::class)->state('album', 'draft')->create();
        $photoA = factory(Photo::class)->create(['number' => 12, 'item_id' => $album->id]);
        $photoB = factory(Photo::class)->create(['number' => 13, 'item_id' => $album->id]);

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos', [
            'filename' => 'photo123.jpg',
            'type' => 'image/jpeg',
            'number' => 12,
        ]);

        $this->seeStatusCode(201);
        $this->assertEquals(12, $this->responseData('data.number'));
        $this->assertEquals(13, $photoA->fresh()->number);
        $this->assertEquals(14, $photoB->fresh()->number);
    }
}
