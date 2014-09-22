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

// restapi/dispatch.php needs to include the classes referenced above.
// In case restapi is symlinked, this lets dispatch.php find the controllers.
$path = __DIR__ . DIRECTORY_SEPARATOR . 'src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include join(DIRECTORY_SEPARATOR, array('.', 'restapi', 'dispatch.php'));
