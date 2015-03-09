<?php

namespace Jeppech\Curl;

/**
 * Description
 *
 * @version 0.0.1
 * @author Jeppe Christiansen <jeppe@codr.dk>
 * @package Response
 */
class Response
{
    /**
     * Contains the message body, without the HTTP message
     *
     * @var string
     */
    protected $body;

    /**
     * Contains the entire raw HTTP response
     *
     * @var string
     */
    protected $raw;

    /**
     * Contains HTTP status
     *
     * @var string
     */
    protected $status;

    /**
     * Contains HTTP status code
     *
     * @var int
     */
    protected $code;

    /**
     * Contains HTTP status message
     *
     * @var string
     */
    protected $status_message;

    /**
     * Contains all response headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Raw HTTP data, without body.
     *
     * @var string
     */
    protected $http_message;

    /**
     * An array containing data for redirects
     *
     * @var array
     */
    protected $redirect_messages = [];

    /**
     * @param string $raw Curl response
     */
    public function __construct($raw)
    {
        $this->raw          = $raw;
        $this->http_message = $this->parseRawHttpMessage();
        $this->body         = $this->getMessageBody();

        $this->parseHttpMessage();
    }

    /**
     * Returns raw HTTP message.
     *
     * @return null|string
     */
    private function parseRawHttpMessage()
    {
        $messages = $this->getHttpMessages();

        if (!empty($messages)) {
            return implode("", $messages);
        }

        return null;
    }

    /**
     * Returns array of http messages
     *
     * @return null|array
     */
    private function getHttpMessages() {
        preg_match_all("#(HTTP/\d\.\d.*?\R\R)#is", $this->raw, $raw_matches);

        if (!empty($raw_matches[0])) {
            return $raw_matches[0];
        }

        return null;
    }

    /**
     * Returns the HTTP message body, by subtracting the
     * @return string
     */
    private function getMessageBody()
    {
        return mb_substr($this->raw, mb_strlen($this->http_message));
    }

    /**
     * Splits apart the HTTP message into their respective parts.
     *
     * @return array
     */
    private function parseHttpMessage()
    {
        $messages = $this->getHttpMessages();

        for ($i = 0, $n = (count($messages) - 1); $i <= $n; $i++) {
            if (empty($messages[$i])) {
                continue;
            }

            if ($i == $n) {
                $this->headers          = $this->getHttpHeaders($messages[$i]);
                $this->code             = $this->getHttpStatusCode($messages[$i]);
                $this->status_message   = $this->getHttpStatusMessage($messages[$i]);
                $this->status           = $this->getHttpStatus($messages[$i]);

                continue;
            }

            array_push($this->redirect_messages, $messages[$i]);
        }
    }

    /**
     * Return HTTP status code, when supplied with raw HTTP message
     *
     * @param string $http_message
     * @return integer
     */
    private function getHttpStatusCode(&$http_message)
    {
        preg_match("/HTTP\/\d\.\d\s(\d{3})/", $http_message, $code);

        if (!empty($code)) {
            return intval($code[1]);
        }

        return 0;
    }

    private function getHttpStatusMessage(&$http_message)
    {
        preg_match("/\d{3}\s(.*[^\r])\r?\n/", $http_message, $status_message);

        if (!empty($status_message)) {
            return $status_message[1];
        }

        return null;
    }

    /**
     * Return HTTP status, when supplied with raw HTTP message
     *
     * @param string $http_message
     * @return null|string
     */
    private function getHttpStatus(&$http_message)
    {
        preg_match("#HTTP/\d\.\d\s(.*[^\r])\r?\n#", $http_message, $status);

        if (!empty($status)) {
            return $status[1];
        }

        return null;
    }

    /**
     * Return array containing a list of HTTP headers
     *
     * @param string $http_message
     * @return null|array
     */
    private function getHttpHeaders(&$http_message)
    {
        preg_match_all("/([A-Za-z0-9-_]+):\s?(.*?)\r?\n/m", $http_message, $headers);

        if (empty($headers)) {
            return null;
        }

        $header_list = [];
        foreach ($headers[1] as $index => $header) {
            if (!isset($header_list[$header])) {
                $header_list[$header] = [];
            }

            array_push($header_list[$header], $headers[2][$index]);
        }

        return $header_list;
    }

    /**
     * Returns HTTP status code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns HTTP status message
     *
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->status_message;
    }

    /**
     * Return HTTP status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Return HTTP headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the raw request
     *
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Returns a new Response object, containing the redirect information.
     *
     * @param integer $index
     * @return Response|null
     */
    public function getRedirect($index)
    {
        if (!is_int($index)) {
            throw new \InvalidArgumentException("1st argument must be integer");
        }

        if (isset($this->redirect_messages[$index])) {
            return new Response($this->redirect_messages[$index]);
        }

        return null;
    }

    /**
     * Return the number of redirects during the request.
     *
     * @return integer
     */
    public function getNumberOfRedirects()
    {
        return count($this->redirect_messages);
    }

    /**
     * Returns raw HTTP message, including redirect messages.
     *
     * @return string
     */
    public function getRawHttpMessage()
    {
        return $this->http_message;
    }

    public function getBody() {
        return $this->body;
    }

    public function __toString()
    {
        return $this->body;
    }
}
