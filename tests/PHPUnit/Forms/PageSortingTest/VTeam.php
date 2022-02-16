<?php
namespace UndefinedOffset\SortableGridField\Tests\Forms\PageSortingTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\Forms\PageSortingTest\VTeam
 *
 * @property string Name
 * @property string City
 * @property int SortOrder
 */
class VTeam extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_PageSortOrder_VTeam';

    private static $db = [
        'Name' => 'Varchar',
        'City' => 'Varchar',
        'SortOrder' => 'Int',
    ];

    private static $default_sort = 'SortOrder';

    private static $extensions = [
        Versioned::class,
    ];
}
