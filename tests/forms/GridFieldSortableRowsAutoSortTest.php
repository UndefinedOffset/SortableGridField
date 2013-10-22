<?php
class GridFieldSortableRowsAutoSortTest extends SapphireTest {

	/** @var ArrayList */
	protected $list;
	
	/** @var GridField */
	protected $gridField;
	
	/** @var Form */
	protected $form;
	
	/** @var string */
	public static $fixture_file = 'GridFieldSortableRowsAutoSortTest.yml';

	/** @var array */
	protected $extraDataObjects = array('GridFieldAction_SortOrder_Player');
	
	public function setUp() {
		parent::setUp();
		$this->list = GridFieldAction_SortOrder_Player::get();
		$config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
		$this->gridField = new GridField('testfield', 'testfield', $this->list, $config);
		$this->form = new Form(new Controller(), 'mockform', new FieldList(array($this->gridField)), new FieldList());
	}
	
	public function testAutoSort() {
		if(Member::currentUser()) { Member::currentUser()->logOut(); }
		
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortableRowsToggle', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		
		//Insure sort ran
		$this->assertEquals(3, $this->list->last()->SortOrder, 'Auto sort should have run');
		
		
		//Check for duplicates (there shouldn't be any)
		$count=$this->list->Count();
		$indexes=count(array_unique($this->list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected');
	}
	
	public function testAppendToTopAutoSort() {
		if(Member::currentUser()) { Member::currentUser()->logOut(); }
		
		$this->gridField->getConfig()->getComponentByType('GridFieldSortableRows')->setAppendToTop(true);
		
		$this->assertEquals(0, $this->list->last()->SortOrder, 'Auto sort should not have run');
		
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortableRowsToggle', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		
		//Insure sort ran
		$this->assertEquals(3, $this->list->last()->SortOrder, 'Auto sort should have run');
		
		
		//Check for duplicates (there shouldn't be any)
		$count=$this->list->Count();
		$indexes=count(array_unique($this->list->column('SortOrder')));
		$this->assertEquals(0, $count-$indexes, 'Duplicate indexes detected');
	}
}

class GridFieldAction_SortOrder_Player extends DataObject implements TestOnly {
	static $db = array(
		'Name' => 'Varchar',
		'SortOrder' => 'Int'
	);
	
	static $default_sort='SortOrder';
}
?>