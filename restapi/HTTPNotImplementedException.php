<?php

namespace restapi;

require_once 'HTTPException.php';

/**
 * Convience class to throw a 501 Not Implemented error.
 * 
 * Example:
 * <code>
 * throw new HTTPNotImplementedException("lookup by gender not yet implemented");
 * // Throws a 501 Not Implemented with the body of the response set to 
 * // "lookup by gender not yet implemented".
 * </code>
 *
 * @author Grant Grueninger
 */
class HTTPNotImplementedException extends HTTPException {

    function __construct($message, $code=null, $previous=null) {

        parent::__construct(new APIResponse(["status_code" => 501, "body" => $message]),
            $code, $previous);
    }

}
