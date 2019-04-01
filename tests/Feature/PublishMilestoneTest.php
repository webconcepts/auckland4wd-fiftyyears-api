<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PublishMilestoneTest extends TestCase
{
    use DatabaseMigrations, PublishItemContractTests;

    protected $itemState = 'milestone';

    protected $itemUrlPath = 'milestones';
}
