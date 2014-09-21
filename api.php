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
