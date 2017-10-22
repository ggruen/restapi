<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace restapi;

// Make sure we catch anything a stricter system will catch
error_reporting(E_ALL);

require_once(__DIR__ . '/../restapi/APIController.php');

/**
 * Concrete instance of the abstract APIController class that APIControllerTest
 * can use to run tests against the abstract class.
 *
 * @author grant
 */
class APIControllerTester extends APIController {
    //put your code here
}
