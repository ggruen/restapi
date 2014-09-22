<?php

namespace restapi;

use Exception;

require_once __DIR__ . '/APIResponse.php';

# What module did they ask for?
# api.php?request=module_name/verb/arg1
$request = filter_input(INPUT_GET, 'request');

if (!$request) {
    header('HTTP/1.0 404 Not Found');
    echo "Missing request parameter";
    return;
}
$resource = explode('/', rtrim($request, '/'))['0'];

if ( !is_array($resource_list) ) {
    throw new Exception("resource_list variable must be defined as an array");
}

if ($resource_list["$resource"]) {

    try {
        $api = get_api_object($resource_list["$resource"], $request);
        $response = $api->dispatchAPI();
        if (is_string($response)) {
            $response = new APIResponse([body => $response]); // Backwards compatibility - deprecated
        }
        if ( $response instanceof APIResponse) {
            // Should all output be done in api.php instead? If so, copy the
            // 2 methods out of APIResponse (since this is the *only* place
            // that should be calling those methods.
            output_response($response);
        } else {
            throw new Exception("Invalid response received from dispatchAPI");
        }
        // TODO: Add an HTTPException catch in here (and an HTTPException class)
        // to throw HTTP status errors.
    } catch (Exception $e) {
        output_response(new APIResponse([status_code => 500,
            body => $e->getMessage()]));
    }
} else {
    output_response(new APIResponse([status_code => 404, body=>'Resource Not Found']));
}

/**
 * Get and return an API object based on the module definition.
 */
function get_api_object($handler, $request) {
    $classfile = "${handler}.php";
    // Requires that the caller (e.g. api.php) included "src" in the
    // include path
    require_once("controller/$classfile" );
    require_once( __DIR__ . '/APIResponse.php');
    $classname = 'restapi\\' . $handler;
    $api = new $classname($request);
    return $api;
}

/**
 * Convenience method to output headers in a centralized place.
 */
function output_headers($api_response) {
    header($api_response->status_header());
    if (is_array($api_response->get_headers())) {
        foreach ($api_response->get_headers() as $header => $value) {
            header($header . ':' . $value);
        }
    }
}

/**
 * While we're at it - convenience method to output the full response
 * as a user agent would expect it.
 */
function output_response($api_response) {
    output_headers($api_response);
    if (!is_null($api_response->get_body())) {
        echo $api_response->get_body();
    } else {
        echo $api_response->get_status_code() . ' ' . $api_response->status_meaning();
    }
}
