<?php

use App\User;
use App\Item;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RemoveDraftPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations, RemoveDraftItemContractTests;

    protected $itemState = 'album';

    protected $itemUrlPath = 'photo-albums';
}
