has_many Example
=================

```php
/*** TestPage.php ***/
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class TestPage extends Page
{
	private static $has_many = [
		'TestObjects' => 'TestObject',
	];

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		
		$conf = GridFieldConfig_RecordEditor::create(10);
		$conf->addComponent(new GridFieldSortableRows('SortOrder'));

		$fields->addFieldToTab('Root.TestObjects', new GridField('TestObjects', 'TestObjects', $this->TestObjects(), $conf));

		return $fields;
	}
}


/*** TestObject.php ***/
use SilverStripe\ORM\DataObject;

class TestObject extends DataObject
{
	private static $db = [
		'Title' => 'Text',
		'SortOrder' => 'Int',
	];

    private static $has_one = [
        'Parent' => 'TestPage',
    ];

	private static $default_sort = 'SortOrder';
}
```
