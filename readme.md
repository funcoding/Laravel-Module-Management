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

For example, consider you have set the module name as User, then this will create the User module under app folder.

Following files are created for each module

|            Files          |              Location            |            TYPE             |          
|---------------------------|----------------------------------|-----------------------------|
| UserController            | (App\User\Http\Controllers)      | Controller                  |
| UserRequest               | (App\User\Http\Requests)         | FormRequest                 |
| User                      | (App\User\Model)                 | Model                       |
| UserRepository            | (App\User\Repository)            | Repository                  |
| UserInterface             | (App\User\Repository)            | Interface                   |
| routes.php                | (App\User\Http)                  | routes file                 |
| RouteServiceProvider      | (App\User\Providers)             | RouteServiceProvider        | 
| UserServiceProvider       | (App\User\Providers)             | Individual Service Provider |

Now you can register the created UserServiceProvider in your config/app.php to enable this module.

Thats it! Thats all you need to enable a module.

#### Creating Table

You can also create table while creating module by adding --migrate=true command

    php artisan make:module User --migrate=true


#### Creating Under namespace

You can also create module under a specific namespace folder

    php artisan make:module User --namespace=Testing

Above command will create our module under Testing folder outside app folder.

If you want it to create inside app folder, then you can specify it like

    php artisan make:module Testing/User

this command will create User module under app/Testing folder