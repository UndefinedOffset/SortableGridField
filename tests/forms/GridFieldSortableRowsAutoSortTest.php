<?php

namespace UndefinedOffset\SortableGridField\Tests;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

/**
 * Class GridFieldSortableRowsAutoSortTest
 *
 * @package SortableGridField\Tests
 */
class GridFieldSortableRowsAutoSortTest extends SapphireTest
{
    /** @var string */
    public static $fixture_file = 'GridFieldSortableRowsAutoSortTest.yml';

    /** @var array */
    protected static $extra_dataobjects = array(
        GridFieldAction_SortOrder_Player::class,
        GridFieldAction_SortOrder_VPlayer::class,
        GridFieldAction_SortOrder_TestParent::class,
        GridFieldAction_SortOrder_BaseObject::class,
        GridFieldAction_SortOrder_ChildObject::class
    );

    public function testAutoSort()
    {
        if (Security::getCurrentUser()) {
            $this->logOut();
        }

        $list = GridFieldAction_SortOrder_Player::get();
        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new SortableGridField_DummyController(), 'mockform', new FieldList(array($gridField)), new FieldList());

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()));
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true))));
        $request->setSession($session);
        
        $gridField->gridFieldAlterAction(array('StateID' => $stateID), $form, $request);

        //Insure sort ran
        $this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run');


        //Check for duplicates (there shouldn't be any)
        $count = $list->Count();
        $indexes = count(array_unique($list->column('SortOrder')));
        $this->assertEquals(0, $count - $indexes, 'Duplicate indexes detected');
    }

    public function testAppendToTopAutoSort()
    {
        if (Security::getCurrentUser()) {
            $this->logOut();
        }

        $list = GridFieldAction_SortOrder_Player::get();
        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new SortableGridField_DummyController(), 'mockform', new FieldList(array($gridField)), new FieldList());

        /** @var GridFieldSortableRows $sortableRows */
        $sortableRows = $gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableRows->setAppendToTop(true);

        $this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run');

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()));
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true))));
        $request->setSession($session);
        $gridField->gridFieldAlterAction(array('StateID' => $stateID), $form, $request);

        //Insure sort ran
        $this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run');


        //Check for duplicates (there shouldn't be any)
        $count = $list->Count();
        $indexes = count(array_unique($list->column('SortOrder')));
        $this->assertEquals(0, $count - $indexes, 'Duplicate indexes detected');
    }

    public function testAutoSortVersioned()
    {
        if (Security::getCurrentUser()) {
            $this->logOut();
        }

        //Force versioned to reset
        Versioned::reset();

        $list = GridFieldAction_SortOrder_VPlayer::get();

        //Publish all records
        foreach ($list as $item) {
            $item->publish('Stage', 'Live');
        }


        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder', true, 'Live'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new SortableGridField_DummyController(), 'mockform', new FieldList(array($gridField)), new FieldList());

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()));
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true))));
        $request->setSession($session);
        $gridField->gridFieldAlterAction(array('StateID' => $stateID), $form, $request);


        //Insure sort ran
        $this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run on Versioned stage "Stage"');


        //Check for duplicates (there shouldn't be any)
        $count = $list->Count();
        $indexes = count(array_unique($list->column('SortOrder')));
        $this->assertEquals(0, $count - $indexes, 'Duplicate indexes detected on Versioned stage "Stage"');


        //Force versioned over to Live stage
        Versioned::set_reading_mode('Live');

        //Get live instance
        $obj = Versioned::get_one_by_stage(GridFieldAction_SortOrder_VPlayer::class, 'Live', '"ID"=' . $list->last()->ID);

        //Insure sort ran
        $this->assertEquals(3, $obj->SortOrder, 'Auto sort should have run on Versioned stage "Live"');


        //Check for duplicates (there shouldn't be any)
        $list = Versioned::get_by_stage(GridFieldAction_SortOrder_VPlayer::class, 'Live');
        $count = $list->Count();
        $indexes = count(array_unique($list->column('SortOrder')));
        $this->assertEquals(0, $count - $indexes, 'Duplicate indexes detected on Versioned stage "Live"');
    }

    public function testAppendToTopAutoSortVersioned()
    {
        if (Security::getCurrentUser()) {
            $this->logOut();
        }

        //Force versioned to reset
        Versioned::reset();

        $list = GridFieldAction_SortOrder_VPlayer::get();

        //Publish all records
        foreach ($list as $item) {
            $item->publish('Stage', 'Live');
        }


        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder', true, 'Live'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new SortableGridField_DummyController(), 'mockform', new FieldList(array($gridField)), new FieldList());

        /** @var GridFieldSortableRows $sortableRows */
        $sortableRows = $gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableRows->setAppendToTop(true);

        $this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run on Versioned stage "Stage"');

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()));
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true))));
        $request->setSession($session);
        $gridField->gridFieldAlterAction(array('StateID' => $stateID), $form, $request);


        //Insure sort ran
        $this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run on Versioned stage "Stage"');


        //Check for duplicates (there shouldn't be any)
        $count = $list->Count();
        $indexes = count(array_unique($list->column('SortOrder')));
        $this->assertEquals(0, $count - $indexes, 'Duplicate indexes detected on Versioned stage "Stage"');


        //Force versioned over to Live stage
        Versioned::set_reading_mode('Live');

        //Insure sort ran
        $this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run on Versioned stage "Live"');


        //Check for duplicates (there shouldn't be any)
        $count = $list->Count();
        $indexes = count(array_unique($list->column('SortOrder')));
        $this->assertEquals(0, $count - $indexes, 'Duplicate indexes detected on Versioned stage "Live"');
    }

    public function testAppendToTopAutoSortChild()
    {
        if (Security::getCurrentUser()) {
            $this->logOut();
        }

        //Push the edit date into the past, we're checking this later
        DB::query('UPDATE "GridFieldAction_SortOrder_BaseObject" SET "LastEdited"=\'' . date('Y-m-d 00:00:00', strtotime('yesterday')) . '\'');

        /** @var GridFieldAction_SortOrder_TestParent $parent */
        $parent = GridFieldAction_SortOrder_TestParent::get()->first();

        /** @var DataList $list */
        $list = $parent->TestRelation();

        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new SortableGridField_DummyController(), 'mockform', new FieldList(array($gridField)), new FieldList());
        $form->loadDataFrom($parent);

        /** @var GridFieldSortableRows $sortableRows */
        $sortableRows = $gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableRows->setAppendToTop(true);

        $this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run');

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()));
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, array('grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => array('GridFieldSortableRows' => array('sortableToggle' => true))));
        $request->setSession($session);
        $gridField->gridFieldAlterAction(array('StateID' => $stateID), $form, $request);

        //Insure sort ran
        $this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run');


        //Check for duplicates (there shouldn't be any)
        $count = $list->Count();
        $indexes = count(array_unique($list->column('SortOrder')));
        $this->assertEquals(0, $count - $indexes, 'Duplicate indexes detected');


        //Make sure the last edited is today for all records
        $this->assertEquals(3, $list->filter('LastEdited:GreaterThan', date('Y-m-d 00:00:00'))->count());
    }
}

/**
 * Class GridFieldAction_SortOrder_Player
 *
 * @package SortableGridField\Tests
 * @property string Name
 * @property int SortOrder
 */
class GridFieldAction_SortOrder_Player extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_Player';

    private static $db = array(
        'Name' => DBVarchar::class,
        'SortOrder' => DBInt::class
    );

    private static $default_sort = 'SortOrder';
}

/**
 * Class GridFieldAction_SortOrder_VPlayer
 *
 * @package SortableGridField\Tests
 * @property string Name
 * @property int SortOrder
 */
class GridFieldAction_SortOrder_VPlayer extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_VPlayer';

    private static $db = array(
        'Name' => DBVarchar::class,
        'SortOrder' => DBInt::class
    );

    private static $default_sort = 'SortOrder';

    private static $extensions = array(
        "SilverStripe\\Versioned\\Versioned('Stage', 'Live')"
    );
}

/**
 * Class GridFieldAction_SortOrder_TestParent
 *
 * @package SortableGridField\Tests
 * @property string Name
 * @method GridFieldAction_SortOrder_ChildObject TestRelation
 */
class GridFieldAction_SortOrder_TestParent extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_TestParent';

    private static $db = array(
        'Name' => DBVarchar::class
    );

    private static $has_many = array(
        'TestRelation' => GridFieldAction_SortOrder_ChildObject::class
    );
}

/**
 * Class GridFieldAction_SortOrder_BaseObject
 *
 * @package SortableGridField\Tests
 * @property string Name
 */
class GridFieldAction_SortOrder_BaseObject extends DataObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_BaseObject';

    private static $db = array(
        'Name' => DBVarchar::class
    );
}

/**
 * Class GridFieldAction_SortOrder_ChildObject
 *
 * @package SortableGridField\Tests
 * @property int SortOrder
 * @method GridFieldAction_SortOrder_TestParent Parent
 */
class GridFieldAction_SortOrder_ChildObject extends GridFieldAction_SortOrder_BaseObject implements TestOnly
{
    private static $table_name = 'GridFieldAction_SortOrder_ChildObject';

    private static $db = array(
        'SortOrder' => DBInt::class
    );

    private static $has_one = array(
        'Parent' => GridFieldAction_SortOrder_TestParent::class
    );

    private static $default_sort = 'SortOrder';
}

/**
 * Class SortableGridField_DummyController
 *
 * @package SortableGridField\Tests
 */
class SortableGridField_DummyController extends Controller
{
    private static $url_segment = 'sortable-grid-field';
}