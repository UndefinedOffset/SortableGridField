<?php
namespace UndefinedOffset\SortableGridField\Tests;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\AutoSortTest\DummyController;
use UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\OrderingTest\Team;
use UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\OrderingTest\VTeam;

class OrderingTest extends SapphireTest
{
    /** @var ArrayList */
    protected $list;

    /** @var GridField */
    protected $gridField;

    /** @var Form */
    protected $form;

    /** @var string */
    public static $fixture_file = 'OrderingTest.yml';

    /** @var array */
    protected static $extra_dataobjects = [
        Team::class,
        VTeam::class
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->list = Team::get();
        $config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
        $this->gridField = new GridField('testfield', 'testfield', $this->list, $config);
        $this->form = new Form(new DummyController(), 'mockform', new FieldList([$this->gridField]), new FieldList());
    }

    public function testSortActionWithoutCorrectPermission()
    {
        if (Security::getCurrentUser()) {
            Injector::inst()->get(IdentityStore::class)->logOut(Controller::curr()->getRequest());
        }

        $this->expectException(ValidationException::class);
        $team1 = $this->objFromFixture(Team::class, 'team1');
        $team2 = $this->objFromFixture(Team::class, 'team2');
        $team3 = $this->objFromFixture(Team::class, 'team3');

        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', ['ItemIDs' => "$team1->ID, $team3->ID, $team2->ID"], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($this->form->getSecurityToken()->getName(), $this->form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'saveGridRowSort', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true]]]);
        $request->setSession($session);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);
        $this->assertEquals($team3->ID, $this->list->last()->ID, 'User should\'t be able to sort records without correct permissions.');
    }

    public function testSortActionWithAdminPermission()
    {
        $team1 = $this->objFromFixture(Team::class, 'team1');
        $team2 = $this->objFromFixture(Team::class, 'team2');
        $team3 = $this->objFromFixture(Team::class, 'team3');
        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', ['ItemIDs' => "$team1->ID, $team3->ID, $team2->ID"], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($this->form->getSecurityToken()->getName(), $this->form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'saveGridRowSort', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true]]]);
        $request->setSession($session);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);
        $this->assertEquals($team2->ID, $this->list->last()->ID, 'User should be able to sort records with ADMIN permission.');
    }

    public function testSortActionVersioned()
    {
        //Force versioned to reset
        Versioned::reset();

        $list = VTeam::get();
        $this->gridField->setList($list);

        /** @var GridFieldSortableRows $sortableGrid */
        $sortableGrid = $this->gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableGrid->setUpdateVersionedStage('Live');

        //Publish all records
        foreach ($list as $item) {
            $item->publishSingle();
        }

        $team1 = $this->objFromFixture(VTeam::class, 'team1');
        $team2 = $this->objFromFixture(VTeam::class, 'team2');
        $team3 = $this->objFromFixture(VTeam::class, 'team3');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', ['ItemIDs' => "$team1->ID, $team3->ID, $team2->ID"], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($this->form->getSecurityToken()->getName(), $this->form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'saveGridRowSort', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true]]]);
        $request->setSession($session);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);

        $this->assertEquals($team2->ID, $list->last()->ID, 'Sort should have happened on Versioned stage "Stage"');

        $list = Versioned::get_by_stage(VTeam::class, 'Live');
        $this->assertEquals($team2->ID, $list->last()->ID, 'Sort should have happened on Versioned stage "Live"');
    }
}
