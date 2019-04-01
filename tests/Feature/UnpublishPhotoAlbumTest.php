<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UnpublishPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations, UnpublishItemContractTests;

    protected $itemState = 'album';

    protected $itemUrlPath = 'photo-albums';
}
