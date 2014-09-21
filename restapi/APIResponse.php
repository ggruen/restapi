<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace restapi;

/**
 * Represents an HTTP response that can be returned by a call to the API.
 * The api.php script expects an APIReponse object to be returned, either
 * via an exception or returned from a method call.  The contents of the
 * APIResponse object will be sent back to the calling system.
 *
 * Examples:
 * <code>
 * public function do_get() {
 *     return new APIResponse(status_code => 200, headers => [],
 *         body => 'I got your request!');
 * }
 * </code>
 * @author grant
 */
class APIResponse {

    /**
     * Stores the HTTP response code for the response.  Must be as defined in
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * 
     * @var INT HTTP response code.
     */
    protected $status_code = 200;

    /**
     * Stores headers that the responder needs to include in the HTTP response.
     * e.g. for a 201 response, a "Location" header is required by RFC2616
     * (see link in status_code section).
     * 
     * @var array Key/Value array; key is header name, value is header value.
     */
    protected $headers = array();

    /**
     * This is the type of content the body contains.  Defaults to text/plain.
     * This is added as the "Content-Type" header if a Content-Type header
     * hasn't been set.
     * 
     * @var string Content-Type header.
     */
    protected $content_type = 'text/plain';

    /**
     * This is the body of the HTTP response, e.g. what a web browser would
     * display.  It can be the human-readable error message, or JSON, HTML,
     * XML, etc.
     * 
     * @var string The body of the HTTP response.
     */
    protected $body = '';

    public function get_status_code() {
        return $this->status_code;
    }

    public function get_headers() {
        $headers = $this->headers;
        if ( ! $headers['Content-Type'] ) {
            $headers[ 'Content-Type'] = $this->content_type;
        }
        return $headers;
    }

    public function get_content_type() {
        return $this->content_type;
    }

    public function set_content_type($content_type) {
        $this->content_type = $content_type;
    }

    public function get_body() {
        return $this->body;
    }

    public function set_status_code(INT $status_code) {
        $this->status_code = $status_code;
    }

    public function set_headers($headers) {
        if (!is_array($headers)) {
            throw Exception("headers must be an array");
        }
        $this->headers = $headers;
    }

    public function set_body($body) {
        if (!is_string($body)) {
            throw Exception("Body must be a string");
        }
        $this->body = $body;
    }

    /**
     * Complete associative array of status codes and their descriptions.
     * 
     * @var array keys are HTTP status codes, values are text descriptions.
     */
    private $HTTP_CODES = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'
    );

    /**
     * Constructor.  Accepts an array of arguments, the keys of which can be:
     * "status_code" (and integer), "headers" (an array), and/or "body"
     * (a string).  See the property descriptions for descriptions of each
     * property - the constructor just maps them into place.
     * 
     * @param array $args
     */
    public function __construct($args) {
        // Aside from sounding like pirates fighting, it loops through the
        // arguments we accept (status_code, headers, and body) and
        // if the caller gave us that key, assign the value to the
        // property of the same name.  We use "array_key_exists", because
        // they could assign "$body = ''" or such if they wanted.
        foreach (array('status_code', 'headers', 'body') as $arg) {
            if (array_key_exists($arg, $args)) {
                $this->{$arg} = $args[$arg];
            }
        }
    }

    /**
     * Convenience method that constructs an initial response header from
     * the object's status code.
     * 
     * Example:
     * <code>
     *     // Assuming 200 response was returned
     *     header($response->status_header());
     *     // Outputs: HTTP/1.1 200 OK
     * </code>
     * 
     * The status meaning is automatically included using the status_meaning()
     * method of the object (which conveniently contains meanings for all
     * HTTP response codes).
     * 
     * @return string The text of a HTTP/1.1 initial response header
     */
    public function status_header() {
        return 'HTTP/1.1 ' . $this->status_code . ' ' . $this->status_meaning();
    }

    /**
     * Translates passed $status_code or $this->status_code into a status
     * meaning.
     * 
     * @param int $status_code Optional status code to get the meaning of.
     */
    public function status_meaning($status_code = null) {
        $status_code = $status_code ? $status_code : $this->status_code;
        return $this->HTTP_CODES[$status_code];
    }


}
