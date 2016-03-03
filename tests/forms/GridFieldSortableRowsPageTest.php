<?php
class GridFieldSortableRowsPageTest extends SapphireTest {

	/** @var ArrayList */
	protected $list;
	
	/** @var GridField */
	protected $gridField;
	
	/** @var Form */
	protected $form;
	
	/** @var string */
	public static $fixture_file = 'GridFieldSortableRowsPageTest.yml';

	/** @var array */
	protected $extraDataObjects = array('GridFieldAction_PageSortOrder_Team', 'GridFieldAction_PageSortOrder_VTeam');
	
	public function setUp() {
		parent::setUp();
		$this->list = GridFieldAction_PageSortOrder_Team::get();
		$config = GridFieldConfig_Base::create(5)->addComponent(new GridFieldSortableRows('SortOrder'));
		$this->gridField = new GridField('testfield', 'testfield', $this->list, $config);
		$this->form = new Form(new Controller(), 'mockform', new FieldList(array($this->gridField)), new FieldList());
	}
	
	public function testSortToNextPage() {
		$this->gridField->State->GridFieldPaginator->currentPage=1;
		
		
		$team3 = $this->objFromFixture('GridFieldAction_PageSortOrder_Team', 'team3');
		
		$this->logInWithPermission('ADMIN');
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortToPage', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true), 'GridFieldPaginator'=>array('currentPage'=>1))));
		$request = new SS_HTTPRequest('POST', 'url', array('ItemID'=>$team3->ID, 'Target'=>'nextpage'), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $this->form->getSecurityToken()->getName()=>$this->form->getSecurityToken()->getValue()));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		
		
		$team6 = $this->objFromFixture('GridFieldAction_PageSortOrder_Team', 'team6');
		$this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page');
		
		$team3 = $this->objFromFixture('GridFieldAction_PageSortOrder_Team', 'team3');
		$this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page');
	}
	
	public function testSortToPrevPage() {
		$this->gridField->State->GridFieldPaginator->currentPage=2;
		
		
		$team7 = $this->objFromFixture('GridFieldAction_PageSortOrder_Team', 'team7');
		
		$this->logInWithPermission('ADMIN');
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortToPage', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true), 'GridFieldPaginator'=>array('currentPage'=>1))));
		$request = new SS_HTTPRequest('POST', 'url', array('ItemID'=>$team7->ID, 'Target'=>'previouspage'), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $this->form->getSecurityToken()->getName()=>$this->form->getSecurityToken()->getValue()));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		
		
		$team7 = $this->objFromFixture('GridFieldAction_PageSortOrder_Team', 'team7');
		$this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page');
		
		$team5 = $this->objFromFixture('GridFieldAction_PageSortOrder_Team', 'team5');
		$this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page');
	}
	
	public function testSortToNextPageVersioned() {
		//Force versioned to reset
		Versioned::reset();
		
		$list=GridFieldAction_PageSortOrder_VTeam::get();
		$this->gridField->setList($list);
		$this->gridField->getConfig()->getComponentByType('GridFieldSortableRows')->setUpdateVersionedStage('Live');
		$this->gridField->State->GridFieldPaginator->currentPage=1;
		
		//Publish all records
		foreach($list as $item) {
			$item->publish('Stage', 'Live');
		}
		
		
		$team3 = $this->objFromFixture('GridFieldAction_PageSortOrder_VTeam', 'team3');
		
		$this->logInWithPermission('ADMIN');
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortToPage', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true), 'GridFieldPaginator'=>array('currentPage'=>1))));
		$request = new SS_HTTPRequest('POST', 'url', array('ItemID'=>$team3->ID, 'Target'=>'nextpage'), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $this->form->getSecurityToken()->getName()=>$this->form->getSecurityToken()->getValue()));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		
		
		$team6 = $this->objFromFixture('GridFieldAction_PageSortOrder_VTeam', 'team6');
		$this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page on Versioned stage "Stage"');
		
		$team3 = $this->objFromFixture('GridFieldAction_PageSortOrder_VTeam', 'team3');
		$this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page on Versioned stage "Stage"');
		
		
		$list=Versioned::get_by_stage('GridFieldAction_PageSortOrder_VTeam', 'Live');
		
		$team6 = $list->byID($team6->ID);
		$this->assertEquals(5, $team6->SortOrder, 'Team 6 Should have moved to the bottom of the first page on Versioned stage "Live"');
		
		$team3 = $list->byID($team3->ID);
		$this->assertEquals(6, $team3->SortOrder, 'Team 3 Should have moved to the top of the second page on Versioned stage "Live"');
	}
	
	public function testSortToPrevPageVersioned() {
		//Force versioned to reset
		Versioned::reset();
		
		$list=GridFieldAction_PageSortOrder_VTeam::get();
		$this->gridField->setList($list);
		$this->gridField->getConfig()->getComponentByType('GridFieldSortableRows')->setUpdateVersionedStage('Live');
		$this->gridField->State->GridFieldPaginator->currentPage=2;
		
		//Publish all records
		foreach($list as $item) {
			$item->publish('Stage', 'Live');
		}
		
		
		$team7 = $this->objFromFixture('GridFieldAction_PageSortOrder_VTeam', 'team7');
		
		$this->logInWithPermission('ADMIN');
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortToPage', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true), 'GridFieldPaginator'=>array('currentPage'=>1))));
		$request = new SS_HTTPRequest('POST', 'url', array('ItemID'=>$team7->ID, 'Target'=>'previouspage'), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $this->form->getSecurityToken()->getName()=>$this->form->getSecurityToken()->getValue()));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		
		
		$team7 = $this->objFromFixture('GridFieldAction_PageSortOrder_VTeam', 'team7');
		$this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page on Versioned stage "Stage"');
		
		$team5 = $this->objFromFixture('GridFieldAction_PageSortOrder_VTeam', 'team5');
		$this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page on Versioned stage "Stage"');
		
		
		$list=Versioned::get_by_stage('GridFieldAction_PageSortOrder_VTeam', 'Live');
		
		$team7 = $list->byID($team7->ID);
		$this->assertEquals(5, $team7->SortOrder, 'Team 7 Should have moved to the bottom of the first page on Versioned stage "Live"');
		
		$team5 = $list->byID($team5->ID);
		$this->assertEquals(6, $team5->SortOrder, 'Team 5 Should have moved to the top of the second page on Versioned stage "Live"');
	}
}

class GridFieldAction_PageSortOrder_Team extends DataObject implements TestOnly {
	static $db = array(
		'Name' => 'Varchar',
		'City' => 'Varchar',
		'SortOrder' => 'Int'
	);
	
	static $default_sort='SortOrder';
}

class GridFieldAction_PageSortOrder_VTeam extends DataObject implements TestOnly {
	static $db = array(
		'Name' => 'Varchar',
		'City' => 'Varchar',
		'SortOrder' => 'Int'
	);
	
	static $default_sort='SortOrder';
	
	static $extensions=array(
		"Versioned('Stage', 'Live')"
	);
}
?>