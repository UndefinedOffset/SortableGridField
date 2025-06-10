<?php
namespace UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\PageSortingTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\PageSortingTest\Team
 *
 * @property string Name
 * @property string City
 * @property int SortOrder
 */
class Team extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_PageSortOrder_Team';

    private static $db = [
        'Name' => 'Varchar',
        'City' => 'Varchar',
        'SortOrder' => 'Int',
    ];

    private static $default_sort = 'SortOrder';
}
