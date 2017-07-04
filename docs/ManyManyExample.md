many_many Example
=================
```php
/*** TestPage.php ***/
class TestPage extends Page {
	private static $many_many=array(
		'TestObjects'=>'TestObject'
	);
	
	private static $many_many_extraFields=array(
		'TestObjects'=>array(
			'SortOrder'=>'Int'
		)
	);
	
	
	public function getCMSFields() {
		$fields=parent::getCMSFields();
		
		$conf=GridFieldConfig_RelationEditor::create(10);
		$conf->addComponent(new GridFieldSortableRows('SortOrder'));
		
		$fields->addFieldToTab('Root.TestObjects', new GridField('TestObjects', 'TestObjects', $this->TestObjects(), $conf));
		
		return $fields;
	}
	
	public function TestObjects() {
		return $this->getManyManyComponents('TestObjects')->sort('SortOrder');
	}
}


/*** TestObject.php ***/
class TestObject extends DataObject {
	private static $db=array(
		'Title'=>'Text'
	);
	
	private static $belongs_many_many=array(
		'TestPages'=>'TestPage'
	);
}
```