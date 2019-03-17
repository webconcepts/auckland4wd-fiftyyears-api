<?php

use App\User;
use App\Item;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RemoveDraftMilestoneTest extends TestCase
{
    use DatabaseMigrations, RemoveDraftItemContractTests;

    protected $itemState = 'milestone';

    protected $itemUrlPath = 'milestones';
}
