<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UnpublishMilestoneTest extends TestCase
{
    use DatabaseMigrations, UnpublishItemContractTests;

    protected $itemState = 'milestone';

    protected $itemUrlPath = 'milestones';
}
