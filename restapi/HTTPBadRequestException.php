<?php

namespace restapi;

require_once 'HTTPException.php';

/**
 * Convience class to throw a 400 Bad Request error.
 * 
 * Example:
 * <code>
 * throw new HTTPBadRequestException("maxnum must be an integer");
 * // Throws a 400 Bad Request with the body of the response set to 
 * // "maxnum must be an integer".
 * </code>
 *
 * @author Grant Grueninger
 */
class HTTPBadRequestException extends HTTPException {

    function __construct($message, $code=null, $previous=null) {
        parent::__construct(new APIResponse([status_code => 400, body => $message]),
            $code, $previous);
    }

}
