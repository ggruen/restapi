<?php

namespace restapi;

require_once 'APIResponse.php';
require_once 'HTTPException.php';
require_once 'HTTPBadRequestException.php';  // Frequently used classes
require_once 'HTTPNotImplementedException.php';  // Frequently used classes

/**
 * Provides methods to pre-process RESTful API requests.
 * 
 * 
 */
abstract class APIController {

    /**
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     * 
     * @var string
     */
    protected $method = '';

    /**
     * The Model requested in the URI. eg: /files
     * 
     * @var string
     */
    protected $endpoint = '';

    /**
     * Any additional URI components after the endpoint
     * has been removed, e.g. an integer ID for the resource.
     * eg: /<endpoint>/<arg0>/<arg1> or /<endpoint>/<arg0>
     * 
     * @var Array
     */
    protected $args = Array();

    /**
     * Holds an array derived from the body of a PUT or POST request.
     * 
     * @var Array
     */
    protected $body = Array();

    /**
     * Stores the input of the PUT request.
     * 
     * @var string
     */
    protected $file = Null;

    /**
     * Carries a PDO database connection that's set automatically by dispatchAPI.
     * 
     * <code>
     * $this->db->query('select * from mytable');
     * </code>
     * 
     * @var PDO
     */
    protected $db = Null;

    /**
     * Storage place for headers to return.  This method is private and must
     * be accessed only using "set_response_headers($headers)" below.
     * This prevents a method in a subclass from accidentally
     * nuking all the headers.
     * 
     * @var array Array of headers to be sent with the HTTP response.
     */
    private $response_headers = array();

    /**
     * Set headers to be sent as part of the response to the HHTP request.
     * This method loops through the array you provide assigning values
     * to the headers.  This prevents a method in a subclass from accidentally
     * nuking all the headers.
     * 
     * @param array $headers
     */
    protected function set_response_headers($headers) {
        if (is_array($headers)) {
            foreach ($headers as $header => $value) {
                $this->response_headers["$header"] = $value;
            }
        }
    }

    /**
     * Constructor.
     * 
     * Allows for CORS, assembles and pre-processes the data.  Accepts the
     * request part of the URL (the argument to the "request" param) and
     * an optional HTTP method and post body.  The optional parameters
     * allow the constructor to be called directly from PHP, e.g. for
     * testing.
     * 
     * @param string $request The RESTful request portion of the URL.
     *        e.g. "endpoint/arg1/arg2"
     * @param string Optional - $method GET, POST, PUT or DELETE
     * @param string Optional - POST body
     */
    public function __construct($request = '', $method = '', $post = '') {
        // $request is required if calling from an API.  This lets controllers
        // be used by other classes as well.
        if (!$request) {
            return;
        }

        $this->set_response_headers([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => '*',
                'Content-Type' => 'application/json'
        ]);

        /* Parse the request.
         * 
         * endpoint/arg1/arg2/...
         * "endpoint" goes into $this->endpoint, and $this->args.
         * 
         * See parse_request_into_properties($request).
         */
        $this->parse_request_into_properties($request);

        /*
         * Set $this->method with the HTTP method (GET, PUT, DELETE, POST).
         */
        $this->extract_http_method($method);

        // Set $this->body with body of PUT and POST
        // respectively.
        $this->extract_http_body($post);
    }

    /**
     * Given the "request" parameter (which is really most of the URL,
     * split it into an endpoint and array of args).  These are
     * stored in properties of the object and NOT returned by the method.
     * 
     * Example:
     * $this->parse_request_into_properties("/hotspot/latlon=37.3,-53.5");
     *   // $this->endpoint === "hotspot"
     *   // $this->args === "latlon=37.3,-53.5"
     * 
     * @param string $request
     */
    public function parse_request_into_properties($request) {
        $this->args = explode('/', rtrim($request, '/'));
        $this->endpoint = array_shift($this->args);
    }

    /**
     * Sets $this->method to the HTTP method of the request (GET, PUT, POST,
     * or DELETE).
     * 
     * Some user agents will pass PUT and DELETE methods in a POST request
     * that contains an HTTP_X_HTTP_METHOD header indicating that the
     * request is actually a PUT or a DELETE.  This method sets $this->method
     * with the request method, whether it's passed correctly or hidden
     * in a POST.  Returns the extracted method for the heck of it.
     * 
     * Accepts an optional $method parameter, which, if passed, will be stored
     * as $this->method - this is to allow direct construction of a controller
     * from PHP (e.g. for testing).
     * 
     * @param string $method HTTP request method (GET, POST, PUT, or DELETE).
     * @throws Exception
     */
    public function extract_http_method($method = '') {

        // Note:
        // https://bugs.php.net/bug.php?id=49184
        // filter_input returns null for values in INPUT_SERVER.
        // http://hu1.php.net/manual/en/function.filter-input.php#77307
        // filter_input(INPUT_SERVER, 'REQUEST_METHOD',
        //   FILTER_VALIDATE_REGEXP, [options => ['regexp' => '/^(GET|PUT|DELETE|POST)$/']]);

        /* @var $passed_method string The HTTP method actually used to make
         *      the HTTP request according to the web server. */
        $passed_method = $method ? $method : $_SERVER['REQUEST_METHOD'];

        // Then check for PUT or DELETE hidden in a POST
        if ($passed_method === 'POST' && \is_string($_SERVER['HTTP_X_HTTP_METHOD'])) {
            /* @var $embdded_method string If the actual HTTP method was a
             * POST and the sender included a PUT or DELETE method in an
             * HTTP_X_HTTP_METHOD header, $embedded_method will contain the
             * contents of the HTTP_X_HTTP_METHOD header.
             */
            $embdded_method = $_SERVER['HTTP_X_HTTP_METHOD'];
        } else {
            $embedded_method = '';
        }

        /* @var $extracted_method string The HTTP method the requester really
         *       had in mind.  This is the one we'll return.
         */
        $extracted_method = $embedded_method ? $embedded_method : $passed_method;

        if (!$extracted_method) {
            throw new HTTPException("Didn't get any valid HTTP method");
        }

        if (!\preg_match('/^(GET|PUT|DELETE|POST)$/', $extracted_method)) {
            throw new HTTPException(
            "HTTP method must be GET, PUT, DELETE, or POST, not "
            . $extracted_method);
        }

        $this->method = $extracted_method;
        return $this->method;
    }

    /**
     * Converts the request body from a PUT or POST into an array.
     * 
     * If the input is from a form post, the array will be key/value
     * pairs as parsed by PHP's "$_POST" super global.  If the input
     * is JSON, the JSON object will be converted into an array structure
     * using json_decode($this->body, TRUE).
     * 
     * The resulting array will be stored in $this->body.
     * 
     * $this->method must already be set to PUT or POST, or extract_http_body
     * will return without doing anything.
     * 
     * The method will look for the body content first in the passed $post
     * parameter (used mostly for testing).
     * 
     * @param mixed $post Either a String (JSON object) or array simulating
     *      the $_POST variable as passed from a form.
     * @return mixed If the HTTP request body contained something that could
     *      be parsed as key/value pairs (e.g. a form) or a JSON object,
     *      returns an array.  Otherwise, returns the raw body of the HTTP
     *      request. Return value is also stored in $this->body.
     */
    public function extract_http_body($post = '', $content_type = '') {
        if (!$this->method === 'PUT' && !$this->method === 'POST') {
            return;
        }

        // First try to get the body
        // $_POST will be empty if the HTTP body isn't field/value
        // pairs.
        // http://stackoverflow.com/questions/7047870/issue-reading-http-request-body-from-a-json-post-in-php
        /* @var $body mixed */
        if ($post) {
            $body = $post;
        } elseif ($_POST) {
            $body = $_POST;
        } else {
            $body = \file_get_contents('php://input');
        }

        // If we weren't passed a content type, get it from $_SERVER.
        // CONTENT_TYPE is only set on POST. 
        // http://fr2.php.net/manual/en/reserved.variables.server.php#110763
        // On some servers, it'll be HTTP_CONTENT_TYPE, not CONTENT_TYPE.
        if (!$content_type) {
            $content_type = $_SERVER['CONTENT_TYPE'] ? $_SERVER['CONTENT_TYPE'] : $_SERVER['HTTP_CONTENT_TYPE'];
        }

        // If they said they gave us JSON, convert what we got to JSON.
        // If it errors, then bad them.
        if ($content_type === 'application/json') {
            // Convert JSON into array
            $body = \json_decode($body, \TRUE);
        }

        $this->body = $body;
        return $body;
    }

    /**
     * Calls "do_delete", "do_post", "do_get" or "do_put" based on
     * the value of $this->method (the HTTP method used to call the
     * API.  These methods are stubbed into this abstract API class
     * and must be overridden by the subclass.  The stub methods will
     * throw an exception if called.
     * 
     * The return value of the called method is converted to JSON and
     * returned to the browser.
     * 
     * @return JSON The value returned by the endpoint method.
     */
// TODO: Make this a loop.  Yes, this makes it clear we're not abusing
// user input, but a loop could too.
    public function dispatchAPI() {
        $allowed = $this->allowed_http_methods();
        if (key_exists($this->method, $allowed)) {
            $this->init_model();
            switch ($this->method) {
                case 'DELETE':
                    return $this->_response($this->do_delete());
                case 'POST':
                    return $this->_response($this->do_post());
                case 'GET':
                    return $this->_response($this->do_get());
                case 'PUT':
                    return $this->_response($this->do_put());
            }
        }

// I'm sorry Dave, I'm afraid I can't let you do that.
// RFC2616 says the 405 response code MUST be accompanied by
// an "Allow" header that defines the HTTP methods the
// requested resource *does* allow.  That's just good parenting,
// after all.
// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        return $this->_response(new APIResponse(['status_code' => 405,
                    'headers' => ['Allow' => implode(', ', array_keys($allowed)),
                            'Content-Type' => 'text/plain'],
                    body => '405 Method Not Allowed: ' . $this->method]));
    }

    /**
     * Scans the "do_*" methods of the class to see which ones the subclass has
     * implemented, which determines which HTTP methods this resource supports.
     * Returns an array whose KEYS are the methods this resource supports
     * (or "allows" in HTTP parlance).  HTTP methods not in the returned list
     * are not allowed.
     * 
     * Example:
     * <code>
     * // If the subclass supported GET and POST
     * $api->allowed_http_methods();
     * // would return [ 'GET' => true, 'POST' => true ]
     * </code>
     * 
     * @return array Allowed methods as keys of an array e.g. [ 'GET' => true, 'POST' => true ]
     */
// TODO: Make this a loop.  Yes, this makes it clear we're not abusing
// user input, but a loop could too.
    public function allowed_http_methods() {
        $allowed = array();
        if ((int) method_exists($this, 'do_delete') > 0) {
            $allowed['DELETE'] = true;
        }

        if ((int) method_exists($this, 'do_post') > 0) {
            $allowed['POST'] = true;
        }

        if ((int) method_exists($this, 'do_get') > 0) {
            $allowed['GET'] = true;
        }
        if ((int) method_exists($this, 'do_put') > 0) {
            $allowed['PUT'] = true;
        }
        return $allowed;
    }

    /**
     * Stub method to load the data model.  This is called by dispatchAPI
     * just before calling the do_get/post/put/delete method.
     * 
     * Subclasses can override this to, for example, connect to a database.
     */
    public function init_model() {
        return null;
    }

    /**
     * Takes either an APIResponse or some other data.  Returns an APIResponse
     * object.  This is called by the dispatchAPI method to package the
     * subclass's reply before returning it to api.php.
     * 
     * @param mixed $data
     * @param int $status
     * @return APIResponse
     */
    private function _response($data, $status = 200) {
        if ($data instanceof APIResponse) {
            // Merge any set headers with headers in the response.
            // This approach means the last headers set win if there's a
            // conflict.
            $this->set_response_headers($data->get_headers());
            $data->set_headers($this->response_headers);
            return $data;
        } else {
            // For convenience/laziness - this lets a subclass just return
            // whatever it wants (which it usually what it'll want to do), and
            // we'll package it in JSON and return it.
            $this->set_response_headers(["Content-Type" => "application/json"]);
            return new APIResponse([status_code => $status,
                    headers => $this->response_headers,
                    body => json_encode($data)]);
        }
    }

    /**
     * Convenience method to clean up form inputs.  It removes leading and
     * trailing whitespace and removes HTML and PHP tags.
     * 
     * Example:
     *   $form_array = $this->cleanInputs($this->body);
     *   print $form_array['name'];  // Prints the cleaned up "name" field.
     * 
     *   $string = $this->cleanInputs("  <a href=\"\">hi!</a>  ");
     *   print $string;  // Prints: hi!
     * 
     * @param String or Array $data
     * @return String or Array The cleaned string or array
     */
    public function cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

}
