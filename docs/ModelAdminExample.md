ModelAdmin implementation Example
=================
Please note this example is written with 3.0.x in mind, if you are using 3.1.x make sure you scope all static properties to private not public.
```php
/**** MyModelAdmin.php ****/
class MyModelAdmin extends ModelAdmin {
    public static $menu_title='My Model Admin';
    public static $url_segment='my-model-admin';
    
    public static $managed_models=array(
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
class MATestObject extends DataObject {
    public static $db=array(
                            'Title'=>'Varchar',
                            'SortOrder'=>'Int'
                        );
    
    public static $default_sort='SortOrder';
}
```