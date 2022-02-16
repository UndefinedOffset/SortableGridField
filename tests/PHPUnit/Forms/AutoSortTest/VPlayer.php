<?php
namespace UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\VPlayer
 *
 * @property string Name
 * @property int SortOrder
 */
class VPlayer extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_VPlayer';

    private static $db = [
        'Name' => 'Varchar',
        'SortOrder' => 'Int',
    ];

    private static $default_sort = 'SortOrder';

    private static $extensions = [
        Versioned::class,
    ];
}
