<?php
namespace UndefinedOffset\SortableGridField\Tests\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\BaseObject;
use UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\ChildObject;
use UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\DummyController;
use UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\Player;
use UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\TestParent;
use UndefinedOffset\SortableGridField\Tests\Forms\AutoSortTest\VPlayer;

class AutoSortTest extends SapphireTest
{
    /** @var string */
    public static $fixture_file = 'AutoSortTest.yml';

    /** @var array */
    protected static $extra_dataobjects = [
        Player::class,
        VPlayer::class,
        TestParent::class,
        BaseObject::class,
        ChildObject::class
    ];

    public function testAutoSort()
    {
        if (Security::getCurrentUser()) {
            $this->logOut();
        }

        $list = Player::get();
        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new DummyController(), 'mockform', new FieldList([$gridField]), new FieldList());

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', [], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true]]]);
        $request->setSession($session);

        $gridField->gridFieldAlterAction(['StateID' => $stateID], $form, $request);

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

        $list = Player::get();
        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new DummyController(), 'mockform', new FieldList([$gridField]), new FieldList());

        /** @var GridFieldSortableRows $sortableRows */
        $sortableRows = $gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableRows->setAppendToTop(true);

        $this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run');

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', [], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true]]]);
        $request->setSession($session);
        $gridField->gridFieldAlterAction(['StateID' => $stateID], $form, $request);

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

        $list = VPlayer::get();

        //Publish all records
        foreach ($list as $item) {
            $item->publishSingle();
        }


        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder', true, 'Live'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new DummyController(), 'mockform', new FieldList([$gridField]), new FieldList());

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', [], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true]]]);
        $request->setSession($session);
        $gridField->gridFieldAlterAction(['StateID' => $stateID], $form, $request);


        //Insure sort ran
        $this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run on Versioned stage "Stage"');


        //Check for duplicates (there shouldn't be any)
        $count = $list->Count();
        $indexes = count(array_unique($list->column('SortOrder')));
        $this->assertEquals(0, $count - $indexes, 'Duplicate indexes detected on Versioned stage "Stage"');


        //Force versioned over to Live stage
        Versioned::set_reading_mode('Live');

        //Get live instance
        $obj = Versioned::get_one_by_stage(VPlayer::class, 'Live', '"ID"=' . $list->last()->ID);

        //Insure sort ran
        $this->assertEquals(3, $obj->SortOrder, 'Auto sort should have run on Versioned stage "Live"');


        //Check for duplicates (there shouldn't be any)
        $list = Versioned::get_by_stage(VPlayer::class, 'Live');
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

        $list = VPlayer::get();

        //Publish all records
        foreach ($list as $item) {
            $item->publishSingle();
        }


        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder', true, 'Live'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new DummyController(), 'mockform', new FieldList([$gridField]), new FieldList());

        /** @var GridFieldSortableRows $sortableRows */
        $sortableRows = $gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableRows->setAppendToTop(true);

        $this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run on Versioned stage "Stage"');

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', [], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true]]]);
        $request->setSession($session);
        $gridField->gridFieldAlterAction(['StateID' => $stateID], $form, $request);


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

        /** @var TestParent $parent */
        $parent = TestParent::get()->first();

        /** @var DataList $list */
        $list = $parent->TestRelation();

        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
        $gridField = new GridField('testfield', 'testfield', $list, $config);
        $form = new Form(new DummyController(), 'mockform', new FieldList([$gridField]), new FieldList());
        $form->loadDataFrom($parent);

        /** @var GridFieldSortableRows $sortableRows */
        $sortableRows = $gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableRows->setAppendToTop(true);

        $this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run');

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', [], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $form->getSecurityToken()->getName() => $form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($form->getSecurityToken()->getName(), $form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortableRowsToggle', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true]]]);
        $request->setSession($session);
        $gridField->gridFieldAlterAction(['StateID' => $stateID], $form, $request);

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
