<?php
namespace UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\Versioned\Versioned;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\AutoSortTest\DummyController;
use UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\PageSortingTest\Team;
use UndefinedOffset\SortableGridField\Tests\PHPUnit\Forms\PageSortingTest\VTeam;

/**
 * Class \UndefinedOffset\SortableGridField\Tests\PHPUnit\PageSortingTest
 */
class PageSortingTest extends SapphireTest
{
    /** @var ArrayList */
    protected $list;

    /** @var GridField */
    protected $gridField;

    /** @var Form */
    protected $form;

    /** @var string */
    public static $fixture_file = 'PageSortingTest.yml';

    /** @var array */
    protected static $extra_dataobjects = [
        Team::class,
        VTeam::class
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->list = Team::get();
        $config = GridFieldConfig_Base::create(5)->addComponent(new GridFieldSortableRows('SortOrder'));
        $this->gridField = new GridField('testfield', 'testfield', $this->list, $config);
        $this->form = new Form(new DummyController(), 'mockform', FieldList::create([$this->gridField]), FieldList::create());
    }

    public function testSortToNextPage()
    {
        $this->gridField->State->GridFieldPaginator->currentPage = 1;

        $team3 = $this->objFromFixture(Team::class, 'team3');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', ['ItemID' => $team3->ID, 'Target' => 'nextpage'], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($this->form->getSecurityToken()->getName(), $this->form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortToPage', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true], 'GridFieldPaginator' => ['currentPage' => 1]]]);
        $request->setSession($session);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);

        $team6 = $this->objFromFixture(Team::class, 'team6');
        $this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page');

        $team3 = $this->objFromFixture(Team::class, 'team3');
        $this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page');
    }

    public function testSortToPrevPage()
    {
        $this->gridField->State->GridFieldPaginator->currentPage = 2;

        $team7 = $this->objFromFixture(Team::class, 'team7');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', ['ItemID' => $team7->ID, 'Target' => 'previouspage'], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($this->form->getSecurityToken()->getName(), $this->form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortToPage', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true], 'GridFieldPaginator' => ['currentPage' => 1]]]);
        $request->setSession($session);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);


        $team7 = $this->objFromFixture(Team::class, 'team7');
        $this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page');

        $team5 = $this->objFromFixture(Team::class, 'team5');
        $this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page');
    }

    public function testSortToNextPageVersioned()
    {
        //Force versioned to reset
        Versioned::reset();

        $list = VTeam::get();
        $this->gridField->setList($list);

        /** @var GridFieldSortableRows $sortableGrid */
        $sortableGrid = $this->gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableGrid->setUpdateVersionedStage('Live');
        $this->gridField->State->GridFieldPaginator->currentPage = 1;

        //Publish all records
        foreach ($list as $item) {
            $item->publishSingle();
        }


        $team3 = $this->objFromFixture(VTeam::class, 'team3');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', ['ItemID' => $team3->ID, 'Target' => 'nextpage'], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($this->form->getSecurityToken()->getName(), $this->form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortToPage', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true], 'GridFieldPaginator' => ['currentPage' => 1]]]);
        $request->setSession($session);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);


        $team6 = $this->objFromFixture(VTeam::class, 'team6');
        $this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page on Versioned stage "Stage"');

        $team3 = $this->objFromFixture(VTeam::class, 'team3');
        $this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page on Versioned stage "Stage"');


        $list = Versioned::get_by_stage(VTeam::class, 'Live');

        $team6 = $list->byID($team6->ID);
        $this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page on Versioned stage "Live"');

        $team3 = $list->byID($team3->ID);
        $this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page on Versioned stage "Live"');
    }

    public function testSortToPrevPageVersioned()
    {
        //Force versioned to reset
        Versioned::reset();

        $list = VTeam::get();
        $this->gridField->setList($list);

        /** @var GridFieldSortableRows $sortableGrid */
        $sortableGrid = $this->gridField->getConfig()->getComponentByType(GridFieldSortableRows::class);
        $sortableGrid->setUpdateVersionedStage('Live');
        $this->gridField->State->GridFieldPaginator->currentPage = 2;

        //Publish all records
        foreach ($list as $item) {
            $item->publishSingle();
        }


        $team7 = $this->objFromFixture(VTeam::class, 'team7');

        $this->logInWithPermission('ADMIN');
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', ['ItemID' => $team7->ID, 'Target' => 'previouspage'], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($this->form->getSecurityToken()->getName(), $this->form->getSecurityToken()->getValue());
        $session->set($stateID, ['grid' => '', 'actionName' => 'sortToPage', 'args' => ['GridFieldSortableRows' => ['sortableToggle' => true], 'GridFieldPaginator' => ['currentPage' => 1]]]);
        $request->setSession($session);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);


        $team7 = $this->objFromFixture(VTeam::class, 'team7');
        $this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page on Versioned stage "Stage"');

        $team5 = $this->objFromFixture(VTeam::class, 'team5');
        $this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page on Versioned stage "Stage"');


        $list = Versioned::get_by_stage(VTeam::class, 'Live');

        $team7 = $list->byID($team7->ID);
        $this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page on Versioned stage "Live"');

        $team5 = $list->byID($team5->ID);
        $this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page on Versioned stage "Live"');
    }
}
