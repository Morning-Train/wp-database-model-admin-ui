# Morningtrain\WP\DatabaseModelAdminUi

Autogenerated Wordpress Admin Tables, for Eloquent Models.


## Table of Contents

- [Introduction](#introduction)
- [Getting Started](#getting-started)
  - [Installation](#installation)
- [Dependencies](#dependencies)
- [Usage](#usage)
- [Credits](#credits)
- [License](#license)


## Introduction


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

Initialize `\Morningtrain\WP\DatabaseModelAdminUi\DatabaseModelAdminUi` with the folder, where all the Eloquent Models is located.

```php
\Morningtrain\WP\DatabaseModelAdminUi\ModelUI::setup(__DIR__ . "/app/Models");
```

### Use on an Eloquent Model

In the start of the Model, there is needed to use som traits.  
The traits that is available is:

- `AdminUi`: Render of the Admin Table
- `Readable`: Add the option, to have a single page for each Model entry

In this example there will be used `AdminUi` and `Readable`.  
We will start out, be make use of the traits, by:

```php
use AdminUi;
use Readable;
```

After this, there is needed a method, to start the init methods, for the traits. In the same method, is where the Admin Table settings is being written:

```php
public function setupAdminUi(): void
    {
        $this->adminUiTableData = [
            'iconUrl' => 'dashicons-admin-site',
            'pageTitle' => __('Cars'),
            'menuTitle' => __('Cars'),
            'capability' => 'manage_options',
            'position' => 101,
            'tableColumns' => [
                'name' => [
                    'title' => __('Name'),
                    'sortable' => true,
                    'searchable' => true
                ],
                'car_model' => [
                    'title' => __('Model'),
                    'searchable' => true,
                ],
            ],
            'searchButtonText' => __('Search')
        ];

        $this->initAdminUi();
        $this->initReadable();
    }
```

All the Admin Table settings is as follows:

- `iconUrl`: Is an icon, that is present in Wordpress Dashicons
- `pageTitle`: What's the option-page page title
- `menuTitle`: What's the option-page menu title
- `capability`: What capability the user needs, to view the Admin Table
- `position`: Placement in the Admin menu
- `tableColumns`: The Admin Table columns, that needs to be shown
  - `name`: The label, that is shown for the column
  - `sortable`: If the column in sortable **(Optional)**
  - `searchable`: If you should be able to search for this column **(Optional)**
- `searchButtonText`: What the button text, on the search button

#### Actions

_None at the moment_


#### Filters

| Hook Name                                                       | Filtered value | Extra parameters                                                                                                            | Description                                                 |
|-----------------------------------------------------------------|----------------|-----------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------|
| wpdbmodeladminui/admin-table/{$slug}/column_name/{$column_name} | null           | **$item**: Model object <br />**$column_name**: A string, with the column name <br />**$adminTable**: The AdminTable object | Return some echo able, to bypass the default value          |
| wpdbmodeladminui/admin-table/{$slug}/row_actions                | array          | **$item**: Model object                                                                                                     | Filter the row actions, that is shown on the primary column |


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
