SortableGridField
=================

Adds drag and drop functionality to SilverStripe 3.0's GridField

## Usage
To enable sorting on a data object add one of the following you your sites _config.php
*mysite/_config.php*

    :::php
    GridFieldSortableObject::add_sortable_class('{ClassName}'); //For has_many relationships
    
    GridFieldSortableObject::add_sortable_many_many_relation('{Owner ClassName}', '{Component Name}'); //For many_many relationships


To enable sorting on the grid field add the following to your grid field's config
*Grid Field Config*

    :::php
    $myGridConfig->addComponent(new GridFieldSortableRows());


## Known Isuses
* Many_many relationship is largely untested and may not work as expected


## Credits
GridFieldSortableObject is based off of dataobject_manager's SortableDataObject class by @unclecheese