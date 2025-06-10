<?php
namespace UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\AutoSortTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\AutoSortTest\TestParent
 *
 * @package SortableGridField\Tests
 * @property string Name
 * @method ChildObject TestRelation
 */
class TestParent extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_TestParent';

    private static $db = [
        'Name' => 'Varchar',
    ];

    private static $has_many = [
        'TestRelation' => ChildObject::class,
    ];
}
