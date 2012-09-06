<?php
class GridFieldSortableRowsTest extends SapphireTest {

	/** @var ArrayList */
	protected $list;
	
	/** @var GridField */
	protected $gridField;
	
	/** @var Form */
	protected $form;
	
	/** @var string */
	public static $fixture_file = 'GridFieldSortableRowsTest.yml';

	/** @var array */
	protected $extraDataObjects = array('GridFieldAction_SortOrder_Team');
	
	public function setUp() {
		parent::setUp();
		$this->list = GridFieldAction_SortOrder_Team::get();
		$config = GridFieldConfig::create()->addComponent(new GridFieldSortableRows('SortOrder'));
		$this->gridField = new GridField('testfield', 'testfield', $this->list, $config);
		$this->form = new Form(new Controller(), 'mockform', new FieldList(array($this->gridField)), new FieldList());
	}
	
	public function testSortActionWithoutCorrectPermission() {
		if(Member::currentUser()) { Member::currentUser()->logOut(); }
		$this->setExpectedException('ValidationException');
		
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'saveGridRowSort', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array('Items'=>'1,3,2'), array('action_gridFieldAlterAction?StateID='.$stateID=>true));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		$this->assertEquals(3, $this->list->last()->ID, 'User should\'t be able to sort records without correct permissions.');
	}
	
	public function testSortActionWithAdminPermission() {
		$this->logInWithPermission('ADMIN');
		$stateID = 'testGridStateActionField';
		Session::set($stateID, array('grid'=>'', 'actionName'=>'saveGridRowSort', 'args'=>array('GridFieldSortableRows'=>array('sortableToggle'=>true))));
		$request = new SS_HTTPRequest('POST', 'url', array('Items'=>'1,3,2'), array('action_gridFieldAlterAction?StateID='.$stateID=>true));
		$this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
		$this->assertEquals(2, $this->list->last()->ID, 'User should be able to sort records with ADMIN permission.');
	}
}

class GridFieldAction_SortOrder_Team extends DataObject implements TestOnly {
	static $db = array(
		'Name' => 'Varchar',
		'City' => 'Varchar',
		'SortOrder' => 'Int'
	);
	
	static $default_sort='SortOrder';
}
?>