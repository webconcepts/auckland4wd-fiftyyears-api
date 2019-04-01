<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PublishVideoTest extends TestCase
{
    use DatabaseMigrations, PublishItemContractTests;

    protected $itemState = 'video';

    protected $itemUrlPath = 'videos';
}
