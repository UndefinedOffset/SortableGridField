<?php
namespace UndefinedOffset\SortableGridField\Tests\Forms\OrderingTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\Forms\OrderingTest\Team
 *
 * @property string Name
 * @property string City
 * @property int SortOrder
 */
class Team extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_Team';

    private static $db = [
        'Name' => 'Varchar',
        'City' => 'Varchar',
        'SortOrder' => 'Int',
    ];

    private static $default_sort = 'SortOrder';
}
