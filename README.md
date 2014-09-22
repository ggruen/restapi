restapi
=======

Lightweight PHP-based RESTful API framework

Overview
========

restapi is a basic RESTful MVC API framework.  Its core classes live in
the restapi directory, and are launched by "api.php", which sets up
routes (resources) and includes restapi/dispatch.php.

Most frameworks require that you learn a whole new language (the framework)
before you can get anything done.

To use restapi, you copy the "restapi" folder into your project, modify
the included api.php routing file (below) and a TemplateController.php file.

It implements MVC like so:

- Model: SQL to your database (although that's optional)
- Controller: php classes you write (modify) based on a template
- View: Your front-end HTML5, iOS, MacOS X, Android, Java, etc app

If you know PHP and SQL, you can get your RESTful API set up in about
30-60 minutes.  (That's a real estimate, not a sales estimate).

Install/Upgrade
=======================

- Download and unzip `restapi`
- Copy `restapi/restapi` into your php project folder (overwrite existing dir
  if upgrading)
- New installation only: copy `restapi/api.php` into your php project folder

Use
=====

Directory Structure
-------------------

- project_dir (you make this)
  - `restapi`  (provided for you)
  - `api.php`  (provided for you)
  - `src`  (you make this)
        - `controller` (you make this)
            - `MyResrourceController.php` (you make this from template)
            - `MyOtherResourceController.php` (you make this from template)
  - tests  (you make this, because you're a good developer ;-)
        - `MyResourceControllerTest.php`
        - `MyOtherResourceControllerTest.php`

api.php
-------

This is the routing file.  On a new installation, you copied it into your project_dir folder in the "Installation/Upgrade" step.  It looks like this:

    <?php
    namespace restapi;
    # List of modules we accept and the classes that handle them.
    # Class must be in ./src/controller named ControllerName.php.
    $resource_list = array(
        # src/controller/MyResourceController.php
        'myresource' => 'MyResrourceController',
        # src/controller/MyOtherResourceController.php
        'myotherresource' => 'MyOtherResourceController',
    );

    include './restapi/dispatch.php';

Access
------

`http://mydomain.com/project_dir/api.php?request=myresource/filter1=something,somethingelse;filter2=something`

Write
=====

- Copy `restapi/templates/TemplateController.php` to
  `project_dir/src/controller/MyResourceController.php`
- Open, review, and edit `project_dir/src/controller/MyResourceController.php`
    - It provides example `do_get()` and `do_post()` methods.  The `do_post`
        method will work "out of the box" by just modifying the table and
        column names.
    - To connect to a database, copy `restapi/templates/DBAPIController.php`
      to `project_dir/src/controller/`.  If you don't want to connect to
      a database, uncomment the different class definition in
      MyResourceController.php.  All your controller classes can subclass
      DBAPIController, so you only need to set that up once.

About/Why
=========

I wrote restapi because:

- I know PHP and SQL
- PHP is everywhere, so it's easy to drop php files onto almost any random
  web server and hit them with a URL in a few minutes
- I know SQL, and I don't want to spend my life writing objects to access
  MySQL databases
- Having access to the database through multiple channels (objects and direct
  SQL access) is just too dangerous.  I'd rather enforce integrity in
  the database directly, or have a single API that accesses it (which restapi
  makes easy assuming you don't give out your DB username and password :).

MISC
====

Tests
-----

    cd restapi
    phpunit --strict tests
    
Launch PHP dev server
---------------------

    php -S localhost:8000
    http://localhost:8000/api.php?request=myresource/filter1=something,somethingelse;filter2=something

.htaccess file for your Apache server
-------------------------------------

Allow access to http://domain.com/api/v1/myresource/filter1=something,somethingelse;filter2=something

    # Use PHP5.4 as default
    AddHandler application/x-httpd-php54 .php

    <IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    #RewriteRule api/v1/(.*)$ api/v1/api.php?request=$1 [QSA,NC,L]
    RewriteRule api/v1/(.*)$ api.php?request=$1 [QSA,NC,L]
    </IfModule>
