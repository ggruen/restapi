<?php
namespace restapi;

use PHPUnit_Framework_TestCase;

require_once(__DIR__.'/../restapi/HTTPBadRequestException.php');

/**
 * Description of HTTPBadRequestExceptionTest
 *
 * @author grant
 */
class HTTPBadRequestExceptionTest extends PHPUnit_Framework_TestCase {

    public function test__construct() {
        // Arrange
        $a = new HTTPBadRequestException("bad wolf");

        // Assert
        $this->assertTrue($a->response->get_body() === 'bad wolf');
    }

    // ...
}