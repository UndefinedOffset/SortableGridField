<?php
namespace UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\FieldType\DBInt;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\ChildObject
 *
 * @package SortableGridField\Tests
 * @property int SortOrder
 * @method TestParent Parent
 */
class ChildObject extends BaseObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_ChildObject';

    private static $db = [
        'SortOrder' => DBInt::class,
    ];

    private static $has_one = [
        'Parent' => TestParent::class,
    ];

    private static $default_sort = 'SortOrder';
}
