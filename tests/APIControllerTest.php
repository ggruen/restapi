<?php

declare(strict_types=1);

namespace restapi;

// Make sure we catch anything a stricter system will catch
error_reporting(E_ALL);

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/./APIControllerTester.php');

/**
 * Description of ApiControllerTest
 *
 * @author grant
 */
class APIControllerTest extends TestCase {

    public function test__construct() {
        // This test just passes if it doesn't error
        $a = new APIControllerTester();
        $this->assertTrue(1 === 1, "Created new APIControllerTester object");
    }

    public function test_extract_http_body() {
        $a = new APIControllerTester();

        // Test JSON parsing
        $a->extract_http_method("POST");
        $json_body = $a->extract_http_body("{ \"hi\": \"there\" }",
            "application/json");
        $this->assertTrue($json_body['hi'] === "there");

        // Test Array parsing
        $a->extract_http_method("POST");
        $keyvalue_body = $a->extract_http_body(["hi" => "there"],
            "application/x-www-form-urlencoded");
        $this->assertTrue($keyvalue_body['hi'] === "there");
        
        // Test text parsing
        $a->extract_http_method("POST");
        $text_body = $a->extract_http_body("Well, hello there.",
            "text/plain");
        $this->assertTrue($text_body === "Well, hello there.");
        
    }

}
