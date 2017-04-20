<?php
class GridFieldSortableRowsAutoSortTest extends SapphireTest {
	/** @var string */
	public static $fixture_file = 'GridFieldSortableRowsAutoSortTest.yml';

	/** @var array */
	protected $extraDataObjects = array(
		'GridFieldAction_SortOrder_Player',
		'GridFieldAction_SortOrder_VPlayer',
		'GridFieldAction_SortOrder_TestParent',
		'GridFieldAction_SortOrder_BaseObject',
		'GridFieldAction_SortOrder_ChildObject'
	);
	
	public function testAutoSort() {
		if(Member::currentUser()) { Member::currentUser()->logOut(); }
		
		$list = GridFieldAction_SortOrder_Player::get();
		$config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
		$gridField = new GridField('testfield', 'testfield', $list, $config);
		$form = new Form(new Controller(), 'mockform', new FieldList(array($gridField)), new FieldList());
		
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortableRowsToggle', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $form->getSecurityToken()->getName()=>$form->getSecurityToken()->getValue()));
		$gridField->gridFieldAlterAction(array('StateID'=>$stateID), $form, $request);
		
		//Insure sort ran
		$this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run');
		
		
		//Check for duplicates (there shouldn't be any)
		$count=$list->Count();
		$indexes=count(array_unique($list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected');
	}
	
	public function testAppendToTopAutoSort() {
		if(Member::currentUser()) { Member::currentUser()->logOut(); }
		
		$list = GridFieldAction_SortOrder_Player::get();
		$config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
		$gridField = new GridField('testfield', 'testfield', $list, $config);
		$form = new Form(new Controller(), 'mockform', new FieldList(array($gridField)), new FieldList());
		
		$gridField->getConfig()->getComponentByType('GridFieldSortableRows')->setAppendToTop(true);
		
		$this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run');
		
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortableRowsToggle', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $form->getSecurityToken()->getName()=>$form->getSecurityToken()->getValue()));
		$gridField->gridFieldAlterAction(array('StateID'=>$stateID), $form, $request);
		
		//Insure sort ran
		$this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run');
		
		
		//Check for duplicates (there shouldn't be any)
		$count=$list->Count();
		$indexes=count(array_unique($list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected');
	}
	
	public function testAutoSortVersioned() {
		if(Member::currentUser()) { Member::currentUser()->logOut(); }
		
		//Force versioned to reset
		Versioned::reset();
		
		$list = GridFieldAction_SortOrder_VPlayer::get();
		
		//Publish all records
		foreach($list as $item) {
			$item->publish('Stage', 'Live');
		}
		
		
		$config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder', true, 'Live'));
		$gridField = new GridField('testfield', 'testfield', $list, $config);
		$form = new Form(new Controller(), 'mockform', new FieldList(array($gridField)), new FieldList());
		
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortableRowsToggle', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $form->getSecurityToken()->getName()=>$form->getSecurityToken()->getValue()));
		$gridField->gridFieldAlterAction(array('StateID'=>$stateID), $form, $request);
		
		
		//Insure sort ran
		$this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run on Versioned stage "Stage"');
		
		
		//Check for duplicates (there shouldn't be any)
		$count=$list->Count();
		$indexes=count(array_unique($list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected on Versioned stage "Stage"');
		
		
		//Force versioned over to Live stage
		Versioned::reading_stage('Live');
		
		//Get live instance
		$obj=Versioned::get_one_by_stage('GridFieldAction_SortOrder_VPlayer', 'Live', '"ID"='.$list->last()->ID);
		
		//Insure sort ran
		$this->assertEquals(3, $obj->SortOrder, 'Auto sort should have run on Versioned stage "Live"');
		
		
		//Check for duplicates (there shouldn't be any)
		$list=Versioned::get_by_stage('GridFieldAction_SortOrder_VPlayer', 'Live');
		$count=$list->Count();
		$indexes=count(array_unique($list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected on Versioned stage "Live"');
	}
	
	public function testAppendToTopAutoSortVersioned() {
		if(Member::currentUser()) { Member::currentUser()->logOut(); }
		
		//Force versioned to reset
		Versioned::reset();
		
		$list = GridFieldAction_SortOrder_VPlayer::get();
		
		//Publish all records
		foreach($list as $item) {
			$item->publish('Stage', 'Live');
		}
		
		
		$config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder', true, 'Live'));
		$gridField = new GridField('testfield', 'testfield', $list, $config);
		$form = new Form(new Controller(), 'mockform', new FieldList(array($gridField)), new FieldList());
		
		$gridField->getConfig()->getComponentByType('GridFieldSortableRows')->setAppendToTop(true);
		
		$this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run on Versioned stage "Stage"');
		
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortableRowsToggle', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $form->getSecurityToken()->getName()=>$form->getSecurityToken()->getValue()));
		$gridField->gridFieldAlterAction(array('StateID'=>$stateID), $form, $request);
		
		
		//Insure sort ran
		$this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run on Versioned stage "Stage"');
		
		
		//Check for duplicates (there shouldn't be any)
		$count=$list->Count();
		$indexes=count(array_unique($list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected on Versioned stage "Stage"');
		
		
		//Force versioned over to Live stage
		Versioned::reading_stage('Live');
		
		//Insure sort ran
		$this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run on Versioned stage "Live"');
		
		
		//Check for duplicates (there shouldn't be any)
		$count=$list->Count();
		$indexes=count(array_unique($list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected on Versioned stage "Live"');
	}
	
	public function testAppendToTopAutoSortChild() {
		if(Member::currentUser()) { Member::currentUser()->logOut(); }
		
		//Push the edit date into the past, we're checking this later
		DB::query('UPDATE "GridFieldAction_SortOrder_BaseObject" SET "LastEdited"=\''.date('Y-m-d 00:00:00', strtotime('yesterday')).'\'');
		
		$parent = GridFieldAction_SortOrder_TestParent::get()->first();
		$list = $parent->TestRelation();
		$config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
		$gridField = new GridField('testfield', 'testfield', $list, $config);
		$form = new Form(new Controller(), 'mockform', new FieldList(array($gridField)), new FieldList());
		$form->loadDataFrom($parent);
		
		$gridField->getConfig()->getComponentByType('GridFieldSortableRows')->setAppendToTop(true);
		
		$this->assertEquals(0, $list->last()->SortOrder, 'Auto sort should not have run');
		
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortableRowsToggle', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $form->getSecurityToken()->getName()=>$form->getSecurityToken()->getValue()));
		$gridField->gridFieldAlterAction(array('StateID'=>$stateID), $form, $request);
		
		//Insure sort ran
		$this->assertEquals(3, $list->last()->SortOrder, 'Auto sort should have run');
		
		
		//Check for duplicates (there shouldn't be any)
		$count=$list->Count();
		$indexes=count(array_unique($list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected');
		
		
		//Make sure the last edited is today for all records
		$this->assertEquals(3, $list->filter('LastEdited:GreaterThan', date('Y-m-d 00:00:00'))->count());
	}
}

class GridFieldAction_SortOrder_Player extends DataObject implements TestOnly {
	static $db = array(
		'Name' => 'Varchar',
		'SortOrder' => 'Int'
	);
	
	static $default_sort='SortOrder';
}

class GridFieldAction_SortOrder_VPlayer extends DataObject implements TestOnly {
	static $db = array(
		'Name' => 'Varchar',
		'SortOrder' => 'Int'
	);
	
	static $default_sort = 'SortOrder';
	
	static $extensions=array(
		"Versioned('Stage', 'Live')"
	);
}

class GridFieldAction_SortOrder_TestParent extends DataObject implements TestOnly {
	static $db = array(
		'Name' => 'Varchar'
	);
	
	static $has_many = array(
		'TestRelation' => 'GridFieldAction_SortOrder_ChildObject'
	);
}

class GridFieldAction_SortOrder_BaseObject extends DataObject implements TestOnly {
	static $db = array(
		'Name' => 'Varchar'
	);
}

class GridFieldAction_SortOrder_ChildObject extends GridFieldAction_SortOrder_BaseObject implements TestOnly {
	static $db = array(
		'SortOrder' => 'Int'
	);
	
	static $has_one = array(
		'Parent'=>'GridFieldAction_SortOrder_TestParent'
	);
	
	static $default_sort='SortOrder';
}
?>