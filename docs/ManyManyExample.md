many_many Example
=================

```php
/*** TestPage.php ***/
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class TestPage extends Page
{
	private static $many_many = [
		'TestObjects' => 'TestObject',
	];

	private static $many_many_extraFields = [
		'TestObjects' => [
			'SortOrder' => 'Int',
		]
	];


	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$conf = GridFieldConfig_RelationEditor::create(10);
		$conf->addComponent(GridFieldSortableRows::create('SortOrder'));

		$fields->addFieldToTab('Root.TestObjects', GridField::create('TestObjects', 'TestObjects', $this->TestObjects(), $conf));

		return $fields;
	}

	public function TestObjects()
	{
		return $this->getManyManyComponents('TestObjects')->sort('SortOrder');
	}
}


/*** TestObject.php ***/
use SilverStripe\ORM\DataObject;

class TestObject extends DataObject
{
	private static $db = [
		'Title' => 'Text',
	];

	private static $belongs_many_many = [
		'TestPages' => 'TestPage',
	];
}
```
