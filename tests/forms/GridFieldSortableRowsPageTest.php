<?php

namespace UndefinedOffset\SortableGridField\Tests;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

/**
 * Class GridFieldSortableRowsPageTest
 *
 * @package SortableGridField\Tests
 */
class GridFieldSortableRowsPageTest extends SapphireTest
{
    /** @var ArrayList */
    protected $list;

    /** @var GridField */
    protected $gridField;

    /** @var Form */
    protected $form;

    /** @var string */
    public static $fixture_file = 'GridFieldSortableRowsPageTest.yml';

    /** @var array */
    protected static $extra_dataobjects = array(
        GridFieldAction_PageSortOrder_Team::class,
        GridFieldAction_PageSortOrder_VTeam::class
    );

    public function setUp()
    {
        parent::setUp();
        $this->list = GridFieldAction_PageSortOrder_Team::get();
        $config = GridFieldConfig_Base::create(5)->addComponent(new GridFieldSortableRows('SortOrder'));
        $this->gridField = new GridField('testfield', 'testfield', $this->list, $config);
        $this->form = new Form(new SortableGridField_DummyController(), 'mockform', FieldList::create(array($this->gridField)), FieldList::create());
    }

    public function testSortToNextPage()
    {
        $this->gridField->State->GridFieldPaginator->currentPage = 1;

        $team3 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_Team', 'team3');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array('ItemID' => $team3->ID, 'Target' => 'nextpage'), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()));
        $session = Injector::inst()->create(Session::class, []);
        $request->setSession($session);
        $session->init($request);
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortToPage', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true), 'GridFieldPaginator' => array('currentPage' => 1))));
        $this->gridField->gridFieldAlterAction(array('StateID' => $stateID), $this->form, $request);

        $team6 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_Team', 'team6');
        $this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page');

        $team3 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_Team', 'team3');
        $this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page');
    }

    public function testSortToPrevPage()
    {
        $this->gridField->State->GridFieldPaginator->currentPage = 2;

        $team7 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_Team', 'team7');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array('ItemID' => $team7->ID, 'Target' => 'previouspage'), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()));
        $session = Injector::inst()->create(Session::class, []);
        $request->setSession($session);
        $session->init($request);
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortToPage', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true), 'GridFieldPaginator' => array('currentPage' => 1))));
        $this->gridField->gridFieldAlterAction(array('StateID' => $stateID), $this->form, $request);


        $team7 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_Team', 'team7');
        $this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page');

        $team5 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_Team', 'team5');
        $this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page');
    }

    public function testSortToNextPageVersioned()
    {
        //Force versioned to reset
        Versioned::reset();

        $list = GridFieldAction_PageSortOrder_VTeam::get();
        $this->gridField->setList($list);

        /** @var GridFieldSortableRows $sortableGrid */
        $sortableGrid = $this->gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableGrid->setUpdateVersionedStage('Live');
        $this->gridField->State->GridFieldPaginator->currentPage = 1;

        //Publish all records
        foreach ($list as $item) {
            $item->publish('Stage', 'Live');
        }


        $team3 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_VTeam', 'team3');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array('ItemID' => $team3->ID, 'Target' => 'nextpage'), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()));
        $session = Injector::inst()->create(Session::class, []);
        $request->setSession($session);
        $session->init($request);
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortToPage', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true), 'GridFieldPaginator' => array('currentPage' => 1))));
        $this->gridField->gridFieldAlterAction(array('StateID' => $stateID), $this->form, $request);


        $team6 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_VTeam', 'team6');
        $this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page on Versioned stage "Stage"');

        $team3 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_VTeam', 'team3');
        $this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page on Versioned stage "Stage"');


        $list = Versioned::get_by_stage(GridFieldAction_PageSortOrder_VTeam::class, 'Live');

        $team6 = $list->byID($team6->ID);
        $this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page on Versioned stage "Live"');

        $team3 = $list->byID($team3->ID);
        $this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page on Versioned stage "Live"');
    }

    public function testSortToPrevPageVersioned()
    {
        //Force versioned to reset
        Versioned::reset();

        $list = GridFieldAction_PageSortOrder_VTeam::get();
        $this->gridField->setList($list);

        /** @var GridFieldSortableRows $sortableGrid */
        $sortableGrid = $this->gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableGrid->setUpdateVersionedStage('Live');
        $this->gridField->State->GridFieldPaginator->currentPage = 2;

        //Publish all records
        foreach ($list as $item) {
            $item->publish('Stage', 'Live');
        }


        $team7 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_VTeam', 'team7');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array('ItemID' => $team7->ID, 'Target' => 'previouspage'), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()));
        $session = Injector::inst()->create(Session::class, []);
        $request->setSession($session);
        $session->init($request);
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortToPage', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true), 'GridFieldPaginator' => array('currentPage' => 1))));
        $this->gridField->gridFieldAlterAction(array('StateID' => $stateID), $this->form, $request);


        $team7 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_VTeam', 'team7');
        $this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page on Versioned stage "Stage"');

        $team5 = $this->objFromFixture('UndefinedOffset\SortableGridField\Tests\GridFieldAction_PageSortOrder_VTeam', 'team5');
        $this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page on Versioned stage "Stage"');


        $list = Versioned::get_by_stage(GridFieldAction_PageSortOrder_VTeam::class, 'Live');

        $team7 = $list->byID($team7->ID);
        $this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page on Versioned stage "Live"');

        $team5 = $list->byID($team5->ID);
        $this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page on Versioned stage "Live"');
    }
}

/**
 * Class GridFieldAction_PageSortOrder_Team
 *
 * @package SortableGridField\Tests
 * @property string Name
 * @property string City
 * @property int SortOrder
 */
class GridFieldAction_PageSortOrder_Team extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_PageSortOrder_Team';

    private static $db = array(
        'Name' => DBVarchar::class,
        'City' => DBVarchar::class,
        'SortOrder' => DBInt::class
    );

    private static $default_sort = 'SortOrder';
}

/**
 * Class GridFieldAction_PageSortOrder_VTeam
 *
 * @package SortableGridField\Tests
 * @property string Name
 * @property string City
 * @property int SortOrder
 */
class GridFieldAction_PageSortOrder_VTeam extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_PageSortOrder_VTeam';

    private static $db = array(
        'Name' => DBVarchar::class,
        'City' => DBVarchar::class,
        'SortOrder' => DBInt::class
    );

    private static $default_sort = 'SortOrder';

    private static $extensions = array(
        "SilverStripe\\Versioned\\Versioned('Stage', 'Live')"
    );
}
