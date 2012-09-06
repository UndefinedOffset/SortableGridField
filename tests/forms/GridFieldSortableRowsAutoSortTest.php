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
		Session::set($stateID, array('grid'=>'', 'actionName'=>'sortableRowsDisablePaginator', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		$this->assertEquals(3, $this->list->last()->SortOrder, 'Auto sort should have run');
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