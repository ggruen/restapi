<?php
declare(strict_types=1);

namespace restapi;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../restapi/HTTPNotImplementedException.php');

/**
 * Description of HTTPBadRequestExceptionTest
 *
 * @author grant
 */
class HTTPNotImplementedExceptionTest extends TestCase {

    public function test__construct() {
        // Arrange
        $a = new HTTPNotImplementedException("bad wolf");

        // Assert
        $this->assertTrue($a->response->get_body() === 'bad wolf');
    }

    // ...
}
