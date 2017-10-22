<?php
declare(strict_types=1);

namespace restapi;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../restapi/HTTPBadRequestException.php');

/**
 * Description of HTTPBadRequestExceptionTest
 *
 * @author grant
 */
class HTTPBadRequestExceptionTest extends TestCase {

    public function test__construct() {
        // Arrange
        $a = new HTTPBadRequestException("bad wolf");

        // Assert
        $this->assertTrue($a->response->get_body() === 'bad wolf');
    }

    // ...
}
