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

    phpunit tests # (in the repository root)
    
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

Note the "v1" in there.  That makes it so that if(when) you need to make a
change to your API a couple years from now (e.g. when `big software company`
wants to buy your service for $X-billion `*`) you just make a new api2.php and
point /api/v2/(.*) at it.

`*` (Please take me for a ride on your jet).

Developing/changing restapi
===========================

Chances are that as you get into building your API, you'll find things you
wish restapi did that it doesn't.  Pull requests are very welcome, especially
where you see "TODO" in the code.

To develop restapi, there are a couple tricks you can use to make things
easy.  Since example is usually the best way to understand code, let's say
your project is in project_dir (as outlined in Directory Structure above),
and that you have `git clone`d restapi into ~/github/restapi.

- Instead of copying `restapi` into your `project_dir`, symlink it
  to your cloned version:
    - `mv project_dir/restapi project_dir/restapi.bkp
    - `ln -s ~/github/restapi/restapi project_dir/restapi`
- If you have a deploy system or script, make sure it **copies** restapi
  to your production server and doesn't just copy (or ignore) the symlink.
- Add `project_dir/restapi` to your *project's* .gitignore file.
- Develop your app.  Make the additions you need to restapi.  restapi will
  still function as though it's in your local app dir.  Note, however, that
  changes you make in restapi will need to be committed in your github
  dir, and that changes you make in your app will be in your `project_dir`.
  Keep this distinction very clear when programming too: Functionality
  you add to restapi must be global (literally - everyone must be able to
  use it) and *not* specific to your app or server.  If you break that rule,
  you 1) won't be able to submit a pull request (or have it accepted at least)
  and 2) won't be able to use your version of restapi in any other of your
  projects.
- Note the `set_include_path` setting in api.php.  If your changes to restapi
  need to reference anything outside the restapi directory, then 1) re-think
  what you're doing, because it's probably app-specific, then 2) if you
  determine it's a good addition to restapi, make sure it uses the include
  path and doesn't expect restapi to be in a specific directory. (and 3,
  maybe you know a better way to do that and can update restapi :).
- **Don't break backwards compatibility!** People need to be able to drop
  the current restapi directory into place in their projects.

When you have a change you want to submit:

- Read git's
  [commit guidelines](http://git-scm.com/book/en/Distributed-Git-Contributing-to-a-Project#Commit-Guidelines]).
  A pull request is basically a "patch", so your commits should:
    - Have one commit per issue
    - Be formatted with a 50-char-or-less first line followed by a blank
      line followed by a descriptive paragraph. Additional paragraphs must
      be separated by blank lines, and bullet points are great.
    - Include motivation and functional change made in the commit message.
    - Use the imperative present tense in the commit message (e.g.
      "Support specific Access-Control-Allow-Origin headers").
- Submit a [pull request](https://help.github.com/articles/using-pull-requests).
