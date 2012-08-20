SortableGridField
=================

Adds drag and drop functionality to SilverStripe 3.0's GridField

## Requirments
* SilverStripe 3.0

## Installation
* Download the module from here https://github.com/UndefinedOffset/SortableGridField/downloads
* Extract the downloaded archive into your site root so that the destination folder is called SortableGridField, opening the extracted folder should contain _config.php in the root along with other files/folders
* Run dev/build?flush=all to regenerate the manifest
* Upon entering the cms and using GridFieldSortableRows component for the first time you make need to add ?flush=all to the end of the address to force the templates to regenerate

## Usage
To enable sorting on a has_many relationship set up an interger field on your data object. Also for has_many relationships make sure to set the $default_sort on the dataobject to this new interger field to ensure that the sort order is applied when the relationship is requested. For many_many relationships you must add a $many_many_extraFields static to the data object defining the relationship, see the SilverStripe documentation for more information on this. If you are using a many_many relationship you will need to do a custom getter to set the sort order of this relationship for use on the front end see bellow for an example. For new DataObjects you do not need to increment the Sort order yourself in your DataObject GridFieldSortableRows will automatically do this the next time the grid is displayed.

    :::php
    public function getMyManyManyRelationship() {
        return $this->getManyManyComponents('MyManyManyRelationship')->sort('SortColumn');
    }


To enable drag and drop sorting on the grid field add the following to your grid field's config
*Grid Field Config*

    :::php
    $myGridConfig->addComponent(new GridFieldSortableRows('{Column to store sort}'));

To move an item to another page drag the row over the respective page button and release.

## @TODO
* Optimize re-ordering of a has_many relationship when sorting on a single page
