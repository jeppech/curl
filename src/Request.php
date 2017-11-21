<?php

namespace Jeppech\Curl;

use Jeppech\Curl\Exceptions\CurlRequestException;
use Jeppech\Filter\Validate;

/**
 * Description
 *
 * @version 0.0.1
 * @author Jeppe Christiansen <jeppe@codr.dk>
 * @package Request
 */
class Request
{
    /**
     * Determines if the request should follow redirects
     *
     * @var bool
     */
    protected $follow_redirects;

    /**
     * Referer header
     *
     * @var string
     */
    protected $referer;

    /**
     * Containing $_SERVER["HTTP_USER_AGENT"] if available, otherwise cURL/{VERSION} PHP/{VERSION}
     *
     * @var string
     */
    protected $user_agent;

    /**
     * Custom headers to pass along with the HTTP request
     *
     * @var array
     */
    protected $headers = array();

    /**
     * HTTP Username
     *
     * @var string
     */
    protected $username;

    /**
     * HTTP Password
     *
     * @var
     */
    protected $password;

    /**
     * Contains resource handle for the cURL request
     *
     * @var resource
     */
    protected $handle;

    /**
     * Stores request data as string
     *
     * @var string
     */
    protected $request_data;

    /**
     * Stores `CURLOPT_` request options
     *
     * @var array
     */
    protected $request_options = array();

    /**
     * Stores path to the cookie file
     *
     * @var string
     */
    protected $cookie_file;

    public function __construct()
    {
        $this->cookie_file  = tempnam("/tmp", "CURLCOOKIE");
        $this->user_agent   = $this->getUserAgent();

        $this->setPropertiesToDefaults();
    }

    /**
     * Perform a HTTP GET request to given $url, with an optional string|array of $request_data
     *
     * @param string $url
     * @param string|array $request_data
     * @return Response
     */
    public function get($url, $request_data = array())
    {
        if (!empty($request_data)) {
            $url .= strpos($url, "?") === false ? "?" : "&";
            $url .= is_array($request_data) ? http_build_query($request_data) : $request_data;
        }

        return $this->request("GET", $url);
    }

    /**
     * Performs a HTTP POST request to a given $url, with an optional array of $request_data
     *
     * @param string $url
     * @param array $request_data
     * @return Response
     */
    public function post($url, $request_data = array())
    {
        return $this->request("POST", $url, $request_data);
    }

    /**
     * Performs a HTTP PUT request to a given $url, with an optional array of $request_data
     *
     * @param string $url
     * @param array $request_data
     * @return Response
     */
    public function put($url, $request_data = array())
    {
        return $this->request("PUT", $url, $request_data);
    }

    /**
     * Performs a HTTP HEAD request to a given $url, with an optional array of $request_data
     *
     * @param string $url
     * @param array $request_data
     * @return Response
     */
    public function head($url, $request_data = array())
    {
        $this->setRequestOption("NOBODY", 1);
        return $this->request("HEAD", $url, $request_data);
    }

    /**
     * Performs a HTTP DELETE request to a given $url, with an optional array of $request_data
     *
     * @param string $url
     * @param array $request_data
     * @return Response
     */
    public function delete($url, $request_data = array())
    {
        return $this->request("DELETE", $url, $request_data);
    }

    /**
      * Performs a custom HTTP request to a given $url with an optional array of $request_data
      * @param string $method
      * @param string $url
      * @param array $request_data
      * @return Response
      *
      * @throws \InvalidArgumentException
      */
    public function request($method, $url, $request_data = array())
    {
        if (is_array($request_data)) {
            $request_data = http_build_query($request_data, '', '&');
        }

        $this->setPostdata($request_data);
        $this->setRequestMethod($method);

        if (Validate::url($url)) {
            $this->applyRequestOptions($url);
        } else {
            throw new \InvalidArgumentException("$url is not a valid URL.");
        }

        $response = $this->executeCurlRequest();
        $this->setPropertiesToDefaults();

        return new Response($response);
    }

    /**
     * Attach optional headers to pass along with the request,
     *
     * @param string|array $header
     * @param string $value
     * @return void
     */
    public function setHeaders($header, $value = null)
    {
        if (is_array($header)) {
            $this->headers = array_merge($this->headers, $header);
        } else {
            $this->headers[$header] = $value;
        }
    }

    /**
     * Stores the request data in the object. If an array
     * is passed, it will be rendered to a string.
     *
     * @param string|array $request_data
     * @return void
     */
    public function setPostdata($request_data)
    {
        if ($this->isJSON($request_data))
        {
            $this->setHeaders("Content-Type", "application/json");
            $this->request_data = $request_data;
        } else if (is_array($request_data)) {
            $this->setHeaders("Content-Type", "application/x-www-form-urlencoded");
            $this->request_data = http_build_query($request_data, '', '&');
        } else if ($request_data) {
           $this->request_data = $request_data;
        }

        $this->setHeaders("Content-Length",strlen($this->request_data));
        return;
    }

    /**
     * Stores cURL request options into $this->request_options, `CURLOPT_` are automatically prepended.
     *
     * @param string $option
     * @param string|array $value
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setRequestOption($option, $value)
    {
        $option = strtoupper(str_replace("CURLOPT_", "", $option));

        if (!defined("CURLOPT_$option")) {
            throw new \InvalidArgumentException("CURLOPT_$option is not a valid constant.");
        }

        $this->request_options[constant("CURLOPT_$option")] = $value;
    }

    /**
     * Sets the CURLOPT_FOLLOWLOCATION to $value
     *
     * @param bool $value
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function followRedirects($value)
    {
        if (!is_bool($value) && !is_int($value)) {
            throw new \InvalidArgumentException("Value must be type of boolean or integer");
        }

        $this->follow_redirects = $value;
    }

    /**
     * Sets referer header in the HTTP request
     *
     * @param string $referer
     * @return void
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }

    /**
     * Sets basic authentication credentials.
     *
     * @param string $username
     * @param string $password
     * @reurn void
     */
    public function setBasicAuth($username, $password)
    {
        $this->setRequestOption("USERPWD", $username.":".$password);
    }

    /**
     * Executes and stores curl request.
     * @throws CurlRequestException
     * @return string
     */
    protected function executeCurlRequest() {
        if (!$response = curl_exec($this->handle)) {
            throw new CurlRequestException(curl_error($this->handle));
        }
        curl_close($this->handle);

        return $response;
    }

    /**
     * Determines the correct cURL request method option to set.
     *
     * @param string $method
     * @return void
     */
    protected function setRequestMethod($method)
    {
        switch (strtoupper($method)) {
            case 'GET':
                $this->setRequestOption("HTTPGET", true);
                break;

            case 'POST':
                $this->setRequestOption("POST", true);
                break;

            case 'HEAD':
                $this->setRequestOption("NOBODY", true);
                break;

            default:
                $this->setRequestOption("CUSTOMREQUEST", strtoupper($method));
                break;
        }
    }

    /**
     * Pass all the request options into the cURL resource.
     *
     * @param  string
     * @param  array
     * @return void
     */
    protected function applyRequestOptions($url)
    {
        $this->setRequestOption("URL", $url);

        if (!empty($this->request_data)) {
            $this->setRequestOption("POSTFIELDS", $this->request_data);
        }

        $headers = array();

        foreach ($this->headers as $header => $value) {
            $headers[] = $header.": ".$value;
        }

        $this->setRequestOption("FOLLOWLOCATION", $this->follow_redirects);
        $this->setRequestOption("HTTPHEADER", $headers);
        $this->initializeCurl();
    }

    protected function initializeCurl() {
        $this->handle = curl_init();
        curl_setopt_array($this->handle, $this->request_options);
    }

    /**
     * Resets object to default options.
     *
     * @return void
     */
    protected function setPropertiesToDefaults()
    {
        $this->follow_redirects = 1;
        $this->request_options  = [];
        $this->request_data     = "";
        $this->headers          = [];

        $this->setRequestOption("HEADER", true);
        $this->setRequestOption("RETURNTRANSFER", true);
        $this->setRequestOption("USERAGENT", $this->user_agent);
        $this->setRequestOption("COOKIEJAR", $this->cookie_file);
        $this->setRequestOption("COOKIEFILE", $this->cookie_file);
    }

    /**
     * Determines the user agent.
     *
     * @return string
     */
    protected function getUserAgent()
    {
        if (isset($_SERVER["HTTP_USER_AGENT"]) && !empty($_SERVER["HTTP_USER_AGENT"])) {
            return $_SERVER["HTTP_USER_AGENT"];
        } else {
            $cURL = curl_version();
            return "cURL/{$cURL["version"]} PHP/".PHP_VERSION." (jeppech/curl)";
        }
    }

    private function isJSON($string)
    {
        return  is_string($string) && is_array(json_decode($string, true)) && 
                (json_last_error() == JSON_ERROR_NONE)
                ? true : false;
    }
}
