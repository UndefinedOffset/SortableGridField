SortableGridField
=================

Adds drag and drop functionality to SilverStripe 3.0's GridField

## Usage
*mysite/_config.php*

  :::php
  GridFieldSortableObject::add_sortable_class('{ClassName}'); //For has_many relationships
  
  GridFieldSortableObject::add_sortable_many_many_relation('{Owner ClassName}', '{Component Name}'); //For many_many relationships
  
*Grid Field Config*
  :::php
  $myGridConfig->addComponent(new GridFieldSortableRows());
  
## Known Isuses
* Many_many relationship is largely untested and may not work as expected
* Documentation on GridFieldSortableObject is very poor


## Credits
GridFieldSortableObject is based off of dataobject_manager's SortableDataObject class by @unclecheese