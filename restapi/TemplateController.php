<?php

namespace restapi;

require_once __DIR__ . '/../../restapi/APIController.php';

use Exception;

/**
 * Template controller class.
 * - Copy this file to src/controller/MyresourceController.php.
 * - Change "class TemplateController" to "class MyresourceController".
 * - Create "do_get", "do_post", "do_put" and/or "do_delete" methods.
 *    - Do NOT create methods for HTTP methods you're not implementing.
 *      e.g. if you're only implementing GET, only create do_get().
 * - Update this documentation to describe what your controller represents.
 * - Add 'myresource' => 'MyresourceController' in api.php.
 * - See it at http://yourdomain.com/myresource/ID-or-filters/args
 * - Profit!
 */
class TemplateController extends APIController {

    /**
     * Handle GET requests.  If you aren't supporting GET for this resource,
     * delete or comment out this method and ApiController will do the
     * right thing for you (send back a 405 Method Not Allowed).
     * 
     * @return type Description
     */
    public function do_get() {
        // http://domain.com/api.php?request=endpoint/args[0]/args[1]/...
        $args = $this->args; // Array, each value contents of next / in URL
        $first_arg = $this->_parse_args($args[0]);
        
        // URL format is up to you, but here's an example.  Given this URL:
        // http://domain.com/api.php?request=myresource/
        //        filter1=something,somethingelse
        // $first_arg == [ filter1 => [ 'something', 'somethingelse' ] ]
        
        // Data Validation - throw an HTTPBadRequestException("message")
        // to generate the appropriate HTTP status code and message.
        if ( $first_arg['filter1'] != 'something' ) {
            throw HTTPBadRequestException("filter1 must be a something");
        }

        // You can return anything (string, array, etc) - gets turned into JSON.
        // This will set the HTTP response body to: { "I Got!" }
        return "I Got!";
    }
    
    /**
     * Handle POST requests.
     *
     * @return APIResponse A 201 HTTP status with URI to the new resource.
     */
    public function do_post() {
        // POST http://domain.com/api.php?request=myresource
        // POST contains fields listed in prepare statement.

        // Sample jQuery call:
        // $.ajax({
        //  url: cscorp.hotspotradar.speedtest.saveUrl,
        //  type: "POST",
        //  dataType: "text",
        //  data: { "something":"is awesome",
        //          "somethingelse":"is better",
        //          "somethingmore":"is too much!"
        //        }}).done(callback).fail(errorcallback);

        // We can safely stuff that POST straight into the database.
        // You can just update the table and column names in the prepare
        // statement and have a functioning do_post method.
        $stmt = $this->db->prepare('insert into mytable('
            . 'something, somethingelse, somethingmore) '
            . 'values (:something, :somethingelse, :somethingmore)'
        );

        $stmt->execute($this->body);
        $resource_id = $this->db->lastInsertId();
        $resource_uri = filter_input(INPUT_SERVER, 'REQUEST_URI')
            . '/' . $resource_id;

        return new APIResponse([
                status_code => 201, headers => [Location => $resource_uri],
                body => $resource_uri
            ]
        );

    }
    
    // Only implement "do_" methods if your resource supports them.
    // HTTP methods that don't exist, if called, will automatically
    // return a 405 with the correct "Allow" header.

    /**
     * RESTful URLs use name=value pairs to delineate named values.
     * names that have a list of values are separated by commas.
     * name=value pairs in a related area are separated by ";"s.
     * 
     * Parses a string of arguments into a key/value array.
     * In the string input, key/value pairs are defined by "key=value",
     * separated by semicolons (";").  Multi-value values are separated by
     * commas (e.g. "latlon=33,22").
     * 
     * _parse_args returns a key/value array.  Multi-value values are returned
     * as indexed arrays.
     * 
     * Example:
     * <code>
     * $this->_parse_args('latlon=33.22,55.33;maxmiles=20')
     * Returns: [ 'latlon' => [ 0 => 33.22, 1 => 55.33 ], 'maxmiles' => 20 ]
     * </code>
     * @param string $string key/value pairs in "key=value1,value2;key2=value2" format.
     * @return array Input transformed into "[key=>[0=>'value1',1=>'value2'], key2=>'value2']" format.
     * @throws Exception
     */
    private function _parse_args($string) {
        if (!$string) {
            return $string;
        }
        if (!is_string($string)) {
            throw new HTTPBadRequestException("Non-string passed to _parse_args: $string");
        }
        // latlon=35.52,-22.55;maxnum=20 gets returned as
        // [latlon => [ 0 => 35.52, 1 => -22.55 ], maxnum => 20]
        $params = [];
        $pairs = explode(';', $string);
        foreach ($pairs as $pair) {
            list($name, $value) = explode('=', $pair, 2);
            if (strpos($value, ',')) {
                $value = explode(',', $value);
            }
            $params["$name"] = $value;
        }

        return $params;
    }

    // Example of throwing respnse for unfinished feature
    private function not_finished_feature() {
        throw new HTTPNotImplementedException("GET by ID not yet supported");
    }

}
