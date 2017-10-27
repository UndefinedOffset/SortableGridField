ModelAdmin implementation Example
=================
```php
/**** MyModelAdmin.php ****/
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class MyModelAdmin extends ModelAdmin {
    private static $menu_title='My Model Admin';
    private static $url_segment='my-model-admin';

    private static $managed_models=array(
                                        'MATestObject'
                                    );

    public function getEditForm($id = null, $fields = null) {
        $form=parent::getEditForm($id, $fields);

        //This check is simply to ensure you are on the managed model you want adjust accordingly
        if($this->modelClass=='MATestObject' && $gridField=$form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if($gridField instanceof GridField) {
                $gridField->getConfig()->addComponent(new GridFieldSortableRows('SortOrder'));
            }
        }

        return $form;
    }
}

/**** MATestObject.php ****/
use SilverStripe\ORM\DataObject;

class MATestObject extends DataObject {
    private static $db=array(
                            'Title'=>'Varchar',
                            'SortOrder'=>'Int'
                        );

    private static $default_sort='SortOrder';
}
```
