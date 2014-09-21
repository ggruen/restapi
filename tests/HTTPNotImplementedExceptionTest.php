<?php
namespace restapi;

use PHPUnit_Framework_TestCase;

require_once(__DIR__.'/../restapi/HTTPNotImplementedException.php');

/**
 * Description of HTTPBadRequestExceptionTest
 *
 * @author grant
 */
class HTTPNotImplementedExceptionTest extends PHPUnit_Framework_TestCase {

    public function test__construct() {
        // Arrange
        $a = new HTTPNotImplementedException("bad wolf");

        // Assert
        $this->assertTrue($a->response->get_body() === 'bad wolf');
    }

    // ...
}
