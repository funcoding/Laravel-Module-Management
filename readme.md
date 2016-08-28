## Laravel Module Management
###### A simple package to set up module management in laravel

This package helps you to create/manage modules in laravel with ease.

### Installation

    composer require sarav/module

### Setup

Register the service provider in config/app.php

    Sarav\Providers\ModuleServiceProvider::class

### Guide

Once you have installed the package, you can create a module by the following command

    php artisan make:module <ModuleName>

For example, consider you have set the module name as User, then this will create the following User module under
app folder.

Following files are created for each module

|            Files          |              Location            |
|---------------------------|----------------------------------|
| UserController            | (App\User\Http\Controllers)      |
| UserRequest               | (App\User\Http\Requests)         |
| User                      | (App\User\Model)                 |
| UserRepository            | (App\User\Repository)            |
| UserInterface             | (App\User\Repository)            |
| routes.php                | (App\User\Http)                  |
| RouteServiceProvider      | (App\User\Providers)             |
| UserServiceProvider       | (App\User\Providers)             |

Now you can register the created UserServiceProvider in your config/app.php to enable this module.

Thats it! Thats all you need to enable a module.

You can also create while creating module by adding --migrate=true command

    php artisan make:module User --migrate=true