SortableGridField
=================

Adds drag and drop functionality to SilverStripe 3.0's GridField

## Usage
To enable sorting on a has_many relationship set up an interger field on your data object.

To enable drag and drop sorting on the grid field add the following to your grid field's config
*Grid Field Config*

    :::php
    $myGridConfig->addComponent(new GridFieldSortableRows('{Column to store sort}'));