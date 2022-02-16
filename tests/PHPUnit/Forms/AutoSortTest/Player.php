<?php
namespace UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\Player
 *
 * @property string Name
 * @property int SortOrder
 */
class Player extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_Player';

    private static $db = [
        'Name' => 'Varchar',
        'SortOrder' => 'Int',
    ];

    private static $default_sort = 'SortOrder';
}
