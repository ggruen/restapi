<?php

namespace restapi;

require_once 'APIResponse.php';
use Exception;

/**
 * An exception that contains an APIResponse object.  This lets methods
 * throw exeptions with rich HTTP status information (e.g. status code,
 * additional headers) back to the user agent.
 *
 * Examples:
 * <code>
 * // Haven't implemeted the "filter=awesome_cafes" feature yet.
 * throw new HTTPException(new APIResponse([status_code=>501,
 *   body=>"Unable to search for awesome cafes - feature not yet implemented."]);
 *
 * // Throw an exeption if the request contains invalid data 
 * throw new HTTPException(new APIResponse([status_code => 400,
 *     body => "maxnum must be an integer."]));
 * </code>
 * 
 * If you pass anything other than an APIResponse object, HTTPException's
 * constructor will re-throw what you do pass as a new Exception.
 * 
 * @author Grant Grueninger
 */
class HTTPException extends Exception {

    public $response;

    public function __construct($response, $code=null, $previous=null) {
        if ($response instanceof APIResponse) {
            $this->response = $response;
            parent::__construct($response->get_body(), $code, $previous);
        } else {
            // Re-throwing takes our name off it - ensures if someone's
            // catching an HTTPException that response will contain an
            // APIResponse.
            throw new Exception( $response, $code, $previous );
        }
    }

}
