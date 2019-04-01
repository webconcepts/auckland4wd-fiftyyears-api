<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PublishPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations, PublishItemContractTests;

    protected $itemState = 'album';

    protected $itemUrlPath = 'photo-albums';
}
