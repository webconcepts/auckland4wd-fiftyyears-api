<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UnpublishVideoTest extends TestCase
{
    use DatabaseMigrations, UnpublishItemContractTests;

    protected $itemState = 'video';

    protected $itemUrlPath = 'videos';
}
