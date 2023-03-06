# Morningtrain\WP\DatabaseModelAdminUi

Autogenerated Wordpress Admin Tables, for Eloquent Models.


## Table of Contents

- [Introduction](#introduction)
- [Getting Started](#getting-started)
  - [Installation](#installation)
- [Dependencies](#dependencies)
- [Usage](#usage)
  - [Initializing package](#initializing-package)
  - [Use for an Eloquent Model](#use-for-an-eloquent-model)
- [Classes](#classes)
  - [ModelPage](#modelpage)
  - [Column](#column)
  - [RowAction](#rowaction)
  - [ViewPage](#viewpage)
  - [AcfEditPage](#acfeditpage)
  - [AcfLoadField](#acfloadfield)
  - [MetaBox](#metabox)
- [Contributing](#contributing)
  - [Bug Report](#bug-report)
  - [Support Questions](#support-questions)
  - [Pull Requests](#pull-requests)
- [Credits](#credits)
- [License](#license)


## Introduction
Make it easy, to create a WordPress Admin Table CRUD.  
**Overview**: Makes a WordPress Admin Table, with the data from the Eloquent Model.  

**Create**: Allows to create a new instance of the Eloquent Model, from an ACF group.  
**Read**: Make a view page, where the data from the instance is displayed.  
**Update**: Allows to update an instance of the Eloquent Model, from an ACF group.  
**Delete**: Allows to delete an instance of the Eloquent Model.

**IMPORTANT**:  
You will need ACF (Advanced Custom Fields) to get the _Create_ and _Update_ parts to work.

## Getting Started

To get started install the package as described below in [Installation](#installation).

To use the tool have a look at [Usage](#usage)


### Installation

Install with composer

```bash
composer require morningtrain/wp-database-model-admin-ui
```


## Dependencies

- [morningtrain/php-loader](https://packagist.org/packages/morningtrain/php-loader)
- [morningtrain/wp-database](https://packagist.org/packages/morningtrain/wp-database)
- [morningtrain/wp-hooks](https://packagist.org/packages/morningtrain/wp-hooks)
- [morningtrain/wp-view](https://packagist.org/packages/morningtrain/wp-view)



## Usage

### Initializing package

Initialize `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI` with the folder, where all the Eloquent Models is located.

```php
\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::setup(__DIR__ . "/app/Models");
```

### Use for an Eloquent Model

When an Admin Table need to be show, for an Eloquent Model, this need to be register in.  
To do this, on each Model class, there will be called a static method called `setupAdminUi`.  
To start of, use `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::modelPage()` method and give it a slug and an Eloquent Model class.  
The wrapper method has the following parameters:

- `string $slug`
- `string $model`


When this is done, we need to register it. This is done by:
```php
\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::modelPage(string, string)
    ->register();
```

This is all there is to get started with an Admin Table overview.  
This will show an Admin Table overview, with all it's columns.  

The ModelPage can be customized, with different things. To se a list of all the settings, see [ModelPage](#modelpage).

## Classes

### ModelPage

##### _Page Title_  
Sets the value to the page title, for the Admin Table.  
Default: `Admin Table`

```php
->withPageTitle(string)
```

##### _Menu Title_
Sets the value to the menu title, for the Admin Table.  
Default: `Admin Table`

```php
->withMenuTitle(string)
```

##### _Capability_
Sets the value, that the user needs, for viewing the Admin Table.  
Default: `manage_options`

```php
->withCapability(string)
```

##### _Icon Url_
Sets the value for the Admin Table admin menu icon.  
Default: **_empty value_**

```php
->withIconUrl(string)
```

##### _Position_
Sets the value for the Admin Table admin menu position.  
Default: `null`

```php
->withPosition(int)
```

##### _Search button text_
Sets the value for the Admin Table search button text.  
Default: `__('Search')`

```php
->withSearchButtonText(string)
```

##### _Columns_
Sets the value as columns, for the Admin Table.  
Default: `all columns on the Model`

This one takes an array of the `Column` classes.  
The `Column` can be customized, with different things. To se a list of all the settings, see [Column](#column).

```php
->withColumns(array)
```

##### _Row Actions_
Sets the value as row actions, for the Admin Table.  
Default: `[]`

If method `->withViewPage()` is in use, it will add a default **View** action to each row.  
If method `->withAcfEditPage()` is in use, it will add a default **Edit** action to each row.  
If method `->makeRemovable()` is in use, it will add a default **Delete** action to each row.

This one takes an array of the `RowAction` classes.  
The `RowAction` can be customized, with different things. To se a list of all the settings, see [RowAction](#rowaction).

```php
->withRowActions(array)
```

##### _Extra Where Clauses Callback_
Return the `callback|string` value, and adds the where clauses to the Eloquent where's.  
Default: `null`

The format for this array, follows the following format:
```php
[
    ['type', 'car']
    ['type', '=', 'car']
]
```

Either of this can be used.

```php
->withAdminTableViews(array)
```

##### _Admin Table Views_
Set up the Admin Table views.  
Default: `[]`

This one takes an array of the `AdminTableViews` classes.  
The `AdminTableViews` can be customized, with different things. To se a list of all the settings, see [AdminTableViews](#admintableview).

```php
->withAdminTableViews(array)
```

##### _View Page_
Set up a view page, for the Admin Table.  
Default: `null`

This one takes an instance of the `ViewPage` class.  
The `ViewPage` can be customized, with different things. To se a list of all the settings, see [ViewPage](#viewpage).

```php
->withViewPage()
```

##### _ACF Create Page_
Set up an ACF create page, for the Admin Table.  
Default: `null`

This one takes an instance of the `AcfCreatePage` class.  
The `AcfCreatePage` can be customized, with different things. To se a list of all the settings, see [AcfCreatePage](#acfcreatepage).

```php
->withAcfCreatePage()
```

##### _ACF Edit Page_
Set up an ACF edit page, for the Admin Table.  
Default: `null`

This one takes an instance of the `AcfEditPage` class.  
The `AcfEditPage` can be customized, with different things. To se a list of all the settings, see [AcfEditPage](#acfeditpage).

```php
->withAcfEditPage()
```


##### _Meta Box_
Render a meta box.  
Default: `null`

This one takes an instance of the `MetaBox` class.  
The `MetaBox` can be customized, with different things. To se a list of all the settings, see [MetaBox](#metabox).

```php
->withMetaBox()
```

##### _Without Columns_
Sets the value as excluded columns, for the Admin Table.  
Default: `[]`

Each item in the array, is the slug on the column.

```php
->withoutColumns(array)
```

##### _Parent Slug_
Sets the value as parent slug, for the Model Page.  
Default: `null`

```php
->makeSubMenu(string)
```

##### _Removable_
Add a removable option.  
Default: `false`

```php
->makeRemovable()
```

---

### Column
To get an instance of a `Column`, use the wrapper method: `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::column()`.  
The wrapper method has the following parameters:

- `string $slug`

##### _Title_
Sets the value to the column title.  
Default: `slug, with first letter uppercase`

```php
->withTitle(string)
```

##### _Render_
Render the `callback|string` in each row, for the specific column.  
Default: `output the value`
Parameters in callback:
- `$instance`
- `ModelPage $modelPage`

```php
->withRender(callback|string)
```

##### _Searchable_
Makes the column searchable.  
Default: `false`

```php
->makeSearchable()
```

##### _Sortable_
Makes the column sortable.  
Default: `false`

```php
->makeSortable()
```

---

### RowAction
To get an instance of a `RowAction`, use the wrapper method: `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::rowAction()`.  
The wrapper method has the following parameters:

- `string $slug`
- `callable|string $renderCallback`: Callback has the following parameters:
  - `array $item`
  - `ModelPage $modelPage`

---

### AdminTableView
To get an instance of a `AdminTableView`, use the wrapper method: `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::adminTableView()`.  
The wrapper method has the following parameters:

- `string $urlKey`
- `null|string $urlValue`

##### _Title_
Sets the value to the view title.  
Default: `urlKey, with first letter uppercase`

```php
->withTitle(string)
```

##### _Count_
Sets the value to the view count.  
Default: `null`

```php
->withCount(string)
```

##### _Count Callback_
Sets the callback value to the view count.  
Default: `null`

```php
->withCountCallback(callback|string)
```

---

### ViewPage
To get an instance of a `ViewPage`, use the wrapper method: `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::viewPage()`.

##### _Render_
Render the `callback|string`, on the view page.  
Default: `shows a table, with all data in a <table>`  
Parameters in callback:
- `array $data`
- `$currentModelPage`

```php
->withRender(callback|string)
```

##### _Capability_
Sets the value to the capability.  
Default: `ModelPage::capability`

```php
->withCapability(string)
```

##### _Hide default view_
Hide the default view.  
Default: `true`

```php
->hideDefaultView()
```

---

### AcfCreatePage
To get an instance of a `AcfCreatePage`, use the wrapper method: `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::acfCreatePage()`.

To make this work, it's needed to create an ACF group, that has the fields, like the Eloquent Model, that can be created.  
Under the ACF group locations, there is a new rule called **Eloquent Model**, that should be chosen to show the ACF group on the create page.

##### _Save Callback_
Calls the `callback|string`, when a Model is updated, through ACF.  
Default: `null`  
Parameters in callback|string:

- `$instance`
- `$model`
- `array $values`

```php
->withSaveCallback(callback|string)
```

##### _Capability_
Sets the value to the capability.  
Default: `ModelPage::capability`

```php
->withCapability(string)
```

---

### AcfEditPage
To get an instance of a `AcfEditPage`, use the wrapper method: `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::acfEditPage()`.

To make this work, it's needed to create an ACF group, that has the fields, like the Eloquent Model, that can be edited.  
Under the ACF group locations, there is a new rule called **Eloquent Model**, that should be chosen to show the ACF group on the edit page.


##### _Load Field Callbacks_
Calls the `AcfLoadField` `callback|string`, when a field, on the Model, is loaded.  
Default: `[]`

This one takes an array of the `AcfLoadField` classes.  
The `AcfLoadField` can be customized, with different things. To se a list of all the settings, see [AcfLoadField](#acfloadfield).

```php
->withLoadFieldCallbacks([])
```

##### _Save Callback_
Calls the `callback|string`, when a Model is updated, through ACF.  
Default: `null`  
Parameters in callback|string:

- `int|null $modelId`
- `$model`
- `array $values`

```php
->withSaveCallback(callback|string)
```

##### _Capability_
Sets the value to the capability.  
Default: `ModelPage::capability`

```php
->withCapability(string)
```

---

### AcfLoadField
To get an instance of a `AcfLoadField`, use the wrapper method: `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::acfLoadField()`.  
The wrapper method has the following parameters:

- `string $slug`
- `callable|string $renderCallback`: Callback has the following parameters:
  - `mixed $return`
  - `string $slug`
  - `int $modelId`
  - `$model`

---

### MetaBox
To get an instance of a MetaBox, use the wrapper method: `\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::metaBox()`.  
The wrapper method has the following parameters:

- `string $slug`
- `callable|string $renderCallback`: Callback has the following parameters:
  - `int|null $modelId`
  - `$model`


##### _Title_
Sets the value to the meta box title.  
Default: `slug, with first letter uppercase`

```php
->withTitle(string)
```

##### _High Priority_
Sets the priority to high.
Default: `default`

```php
->withHighPriority()
```

##### _Core Priority_
Sets the priority to core.
Default: `default`

```php
->withCorePriority()
```

##### _Low Priority_
Sets the priority to low.
Default: `default`

```php
->withLowPriority()
```

##### _Side Context_
Sets the context to side.
Default: `normal`

```php
->onSideContext()
```

##### _On ACF Edit Page_
Sets the meta box to be rendered on Acf Edit Page.  
Default: `Admin Table`

```php
->onAcfEditPage()
```


## Contributing

Thank you for your interest in contributing to the project.


### Bug Report

If you found a bug, we encourage you to make a pull request.

To add a bug report, create a new issue. Please remember to add a telling title, detailed description and how to reproduce the problem. 


### Support Questions

We do not provide support for this package.


### Pull Requests

1. Fork the Project
2. Create your Feature Branch (git checkout -b feature/AmazingFeature)
3. Commit your Changes (git commit -m 'Add some AmazingFeature')
4. Push to the Branch (git push origin feature/AmazingFeature)
5. Open a Pull Request


## Credits

- [Mathias Bærentsen](https://github.com/matbaek)
- [All Contributors](../../contributors)


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.


---

<div align="center">
Developed by <br>
</div>
<br>
<div align="center">
<a href="https://morningtrain.dk" target="_blank">
<img src="https://morningtrain.dk/wp-content/themes/mtt-wordpress-theme/assets/img/logo-only-text.svg" width="200" alt="Morningtrain logo">
</a>
</div>
