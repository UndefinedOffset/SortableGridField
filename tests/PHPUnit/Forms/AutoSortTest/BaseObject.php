<?php
namespace UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\BaseObject
 *
 * @property string Name
 */
class BaseObject extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_BaseObject';

    private static $db = [
        'Name' => 'Varchar',
    ];
}
