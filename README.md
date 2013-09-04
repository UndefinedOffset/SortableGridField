SortableGridField
=================
[![Build Status](https://travis-ci.org/UndefinedOffset/SortableGridField.png)](https://travis-ci.org/UndefinedOffset/SortableGridField)

Adds drag and drop functionality to SilverStripe 3's GridField

## Requirements
* SilverStripe 3.x

## Installation
* Download the module from here https://github.com/UndefinedOffset/SortableGridField/archive/master.zip
* Extract the downloaded archive into your site root so that the destination folder is called SortableGridField, opening the extracted folder should contain _config.php in the root along with other files/folders
* Run dev/build?flush=all to regenerate the manifest
* Upon entering the cms and using GridFieldSortableRows component for the first time you make need to add ?flush=all to the end of the address to force the templates to regenerate

## Usage
To enable sorting on a has_many relationship set up an integer field on your data object. Also for has_many relationships make sure to set the $default_sort on the dataobject to this new integer field to ensure that the sort order is applied when the relationship is requested. For many_many relationships you must add a $many_many_extraFields static to the data object defining the relationship, see the SilverStripe documentation for more information on this. If you are using a many_many relationship you will need to do a custom getter to set the sort order of this relationship for use on the front end see below for an example. For new DataObjects you do not need to increment the Sort order yourself in your DataObject GridFieldSortableRows will automatically do this the next time the grid is displayed.

```php
public function getMyManyManyRelationship() {
    return $this->getManyManyComponents('MyManyManyRelationship')->sort('SortColumn');
}
```


To enable drag and drop sorting on the grid field add the following to your grid field's config
*Grid Field Config*

```php
$myGridConfig->addComponent(new GridFieldSortableRows('{Column to store sort}'));
```

To move an item to another page drag the row over the respective page button and release.

#### Full code Examples
* [has_many relationship] (https://github.com/UndefinedOffset/SortableGridField/blob/master/docs/HasManyExample.md)
* [many_many relationship] (https://github.com/UndefinedOffset/SortableGridField/blob/master/docs/ManyManyExample.md)
* [ModelAdmin implementation] (https://github.com/UndefinedOffset/SortableGridField/blob/master/docs/ModelAdminExample.md)

#### Events
GridFieldSortableRows provides 4 "events" onBeforeGridFieldRowSort(), onAfterGridFieldRowSort(), onBeforeGridFieldPageSort() and onAfterGridFieldPageSort(). These "events" are passed a clone of the DataList used in GridFieldSortableRows, in the case of page sorting this list has a limit that shows you the current page plus/minus one object. For GridFieldSortableRows that are on ModelAdmin decendents these events are called on the ModelAdmin if they do not have a owner DataObject, if you are using GridFieldSortableRows on a GridField for a DataObject's relationship the events are called on that DataObject.

## Migrating from SilverStripe 2.4 and Data Object Manager's SortableDataObject
SortableGridField is not the same as SortableDataObject, since it is only a component of GridField it does not have the ability to catch the object when it is saved for the first time. So SortableGridField uses 1 as the first sort index because 0 is the default for an integer field/column in the database. For migrations from 2.4 with SortableDataObject you need to setup your DataObject based on the instructions above however you must name your sort column "SortOrder" to maintain your sort indexes defined by SortableDataObject. Then you need to run the following query on the table containing your sort field, for many_many relationships this will be something like {RelationshipClass}_{RelationshipName}. This query will maintain your sort order from SortableDataObject but increment the index by 1 giving it a starting number of 1.

```sql
UPDATE YourTable SET SortOrder=SortOrder+1;
```

## Reporting an issue
When you're reporting an issue please ensure you specify what version of SilverStripe you are using i.e. 3.0.5, 3.1beta3, 3.0-master etc. Also be sure to include any JavaScript or PHP errors you receive, for PHP errors please ensure you include the full stack trace. Also please include your implementation code (where your setting up your grid field) as well as how you produced the issue. You may also be asked to provide some of the classes to aid in re-producing the issue. Stick with the issue, remember that you seen the issue not the maintainer of the module so it may take allot of questions to arrive at a fix or answer.

### Notes
* When using with GridFieldManyRelationHandler make sure that you add GridFieldSortableRows to your config before for example GridFieldManyRelationHandler:

    ```php
    $config->addComponent(new GridFieldSortableRows('SortOrder'), 'GridFieldManyRelationHandler');
    ```