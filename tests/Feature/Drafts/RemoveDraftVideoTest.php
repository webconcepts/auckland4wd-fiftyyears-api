<?php

use App\User;
use App\Item;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RemoveDraftVideoTest extends TestCase
{
    use DatabaseMigrations, RemoveDraftItemContractTests;

    protected $itemState = 'video';

    protected $itemUrlPath = 'videos';
}
