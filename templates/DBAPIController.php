<?php

namespace restapi;

require_once __DIR__ . '/../../restapi/APIController.php';

use PDO;  // Includes PDO in this namespace.

use Exception;

/**
 * Parent class of controllers who need DB access.  If your API needs to
 * connect to a database, you can:
 * - Copy this class into your project_dir/src/controller
 * - Add your database connection into in the init_model method
 * - In your project_dir/src/controller/MyResourceController.php file(s),
 *   change the "class MyResourceController extends APIController {" line(s) to
 *   "class MyResourceControler extends DBAPIController {"
 *
 * @author grant
 */
class DBAAPIController extends APIController {
    
    /**
     * Create a new PDO object and connect it to the database.
     * 
     * TODO: Need to be able to connect to live or dev databases.  Probably
     *       should create a db_config parameter that classes can set before
     *       this gets called.
     */
    public function init_model() {
        // Set up a DB connection for the API subclass to use
        $this->db = new PDO('mysql:host=127.0.0.1;dbname=MY_DATABASE_NAME',
            'DB_USERNAME', 'DB_PASSWORD');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // This sets the default return style to column_name => value
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
}
