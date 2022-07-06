SortableGridField
=================

[![CI](https://github.com/UndefinedOffset/SortableGridField/actions/workflows/ci.yml/badge.svg)](https://github.com/UndefinedOffset/SortableGridField/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/undefinedoffset/sortablegridfield/version.svg)](http://www.silverstripe.org/stable-download/)
[![Latest Unstable Version](https://poser.pugx.org/undefinedoffset/sortablegridfield/v/unstable.svg)](https://packagist.org/packages/undefinedoffset/sortablegridfield)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)
[![Total Downloads](https://poser.pugx.org/undefinedoffset/sortablegridfield/downloads.svg)](https://packagist.org/packages/undefinedoffset/sortablegridfield)
[![License](https://poser.pugx.org/undefinedoffset/sortablegridfield/license.svg)](https://github.com/UndefinedOffset/SortableGridField/blob/master/LICENSE)

Adds drag and drop functionality to SilverStripe 4's GridField

## Requirements

* SilverStripe 4.11+

## Installation

Installation is supported via composer only

```sh
composer require undefinedoffset/sortablegridfield
```

* Run `dev/build?flush=all` to regenerate the manifest
* Upon entering the cms and using `GridFieldSortableRows` component for the first time you make need to add `?flush=all`
to the end of the address to force the templates to regenerate

## Usage

To enable sorting on a `has_many` relationship set up an integer field on your data object. Also for `has_many`
relationships make sure to set the `$default_sort` on the `DataObject` to this new integer field to ensure that the sort
order is applied when the relationship is requested. For `many_many` relationships you must add a
`$many_many_extraFields` static to the `DataObject` defining the relationship, see the
[SilverStripe documentation](https://docs.silverstripe.org/en/3/developer_guides/model/relations/#many-many)
for more information on this. If you are using a `many_many` relationship you will need to do a custom getter to set the
sort order of this relationship for use on the front end see below for an example. As well for `many_many` relationships
the name of the GridField *must* be the same as the relationship name other wise error's will occur. For new
`DataObject`s you do not need to increment the sort order yourself in your `DataObject`, `GridFieldSortableRows` will
automatically do this the next time the grid is displayed.

```php
public function getMyManyManyRelationship() {
    return $this->getManyManyComponents('MyManyManyRelationship')->sort('SortColumn');
}
```

To enable drag and drop sorting on the `GridField` add the following to your `GridField`'s config, also make sure you add
the namespace `UndefinedOffset\SortableGridField\Forms` to your file.

```php
//Namespace imports should be added to the top of your file
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

$myGridConfig->addComponent(new GridFieldSortableRows('{Column to store sort}'));
```

To move an item to another page drag the row over the respective move to page button which appear on the left and right
of the GridField and release.

#### Full code Examples

* [has_many relationship](https://github.com/UndefinedOffset/SortableGridField/blob/master/docs/HasManyExample.md)
* [many_many relationship](https://github.com/UndefinedOffset/SortableGridField/blob/master/docs/ManyManyExample.md)
* [ModelAdmin implementation](https://github.com/UndefinedOffset/SortableGridField/blob/master/docs/ModelAdminExample.md)

#### Events

`GridFieldSortableRows` provides 4 "events" `onBeforeGridFieldRowSort()`, `onAfterGridFieldRowSort()`,
`onBeforeGridFieldPageSort()` and `onAfterGridFieldPageSort()`. These "events" are passed a clone of the `DataList`
used in `GridFieldSortableRows`, in the case of page sorting this list has a limit that shows you the current page
plus/minus one object. For `GridFieldSortableRows` that are on `ModelAdmin` descendants these events are called on the
`ModelAdmin` if they do not have an owner `DataObject`, if you are using `GridFieldSortableRows` on a `GridField` for a
`DataObject`'s relationship the events are called on that `DataObject`.

#### Appending to the top instead of the bottom

By default `GridFieldSortableRows` appends to the bottom of the list for performance on large data sets, however you can
set new records to append new records to the top by calling `setAppendToTop(true)` on your `GridFieldSortableRows`
instance.

```php
//Namespace imports should be added to the top of your file
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

$myGridConfig->addComponent($sortable = new GridFieldSortableRows('SortOrder'));
$sortable->setAppendToTop(true);
```

#### Working with versioned records

By default `GridFieldSortableRows` does not update any other stage for versioned than the base stage. However you can
enable this by calling `setUpdateVersionedStage()` and passing in the name of the stage you want to update along with
the base stage. For example passing in "Live" will also update the "Live" stage when any sort happens.

```php
//Namespace imports should be added to the top of your file
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

$myGridConfig->addComponent($sortable = new GridFieldSortableRows('SortOrder'));
$sortable->setUpdateVersionedStage('Live');
```

#### Overriding the default relationship name

By default the relationship name comes from the name of the `GridField`, however you can override this lookup by
calling `setCustomRelationName()` and passing in the name of the relationship. This allows for you to have multiple
`GridFields` on the same form interacting with the same `many_many` list maybe filtered slightly differently.

```php
//Namespace imports should be added to the top of your file
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

$myGridConfig->addComponent($sortable = new GridFieldSortableRows('SortOrder'));
$sortable->setCustomRelationName('MyRelationship');
```

## Reporting an issue

When you're reporting an issue please ensure you specify what version of SilverStripe you are using i.e. 3.0.5,
3.1beta3, 3.0-master etc. Also be sure to include any JavaScript or PHP errors you receive, for PHP errors please ensure
you include the full stack trace. Also please include your implementation code (where your setting up your grid field)
as well as how you produced the issue. You may also be asked to provide some of the classes to aid in re-producing the
issue. Stick with the issue, remember that you seen the issue not the maintainer of the module so it may take a lot of
questions to arrive at a fix or answer.

### Notes

* When using with GridFieldManyRelationHandler make sure that you add GridFieldSortableRows to your config before for
example `GridFieldManyRelationHandler`:

```php
//Namespace imports should be added to the top of your file
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

$config->addComponent(new GridFieldSortableRows('SortOrder'), 'GridFieldManyRelationHandler');
```

## Contributing

### Translations

Translations of the natural language strings are managed through a third party translation interface, transifex.com.
Newly added strings will be periodically uploaded there for translation, and any new translations will be merged back to
the project source code.

Please use [https://www.transifex.com/projects/p/silverstripe-sortablegridfield](https://www.transifex.com/projects/p/silverstripe-sortablegridfield)
to contribute translations, rather than sending pull requests with YAML files.
