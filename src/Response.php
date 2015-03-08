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
    protected $status;

    /**
     * Contains all response headers
     *
     * @var array
     */
    protected $headers = array();

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
    protected $redirect_messages = array();

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
     * @throws \RuntimeException
     */
    private function explodeHttpMessage()
    {
        $messages = $this->getHttpMessages();

        for ($i = 0, $n = (count($messages) - 1); $i <= $n; $i++) {
            if (empty($messages[$i])) {
                continue;
            }

            $status     = $this->getHttpStatus($messages[$i]);
            $code       = $this->getHttpStatusCode($messages[$i]);
            $headers    = $this->getHttpHeaders($messages[$i]);

            if ($i == $n) {
                $this->headers  = $this->getHttpHeaders($message_blocks[$i]);
                $this->code     = $this->getHttpStatusCode($message_blocks[$i]);
                $this->status   = $this->getHttpStatus($message_blocks[$i]);

                continue;
            }

            array_push($this->redirect_messages, $message_blocks[$i]);
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

    /**
     * Return HTTP status message, when supplied with raw HTTP message
     *
     * @param string $http_message
     * @return null|string
     */
    private function getHttpStatus(&$http_message)
    {
        preg_match("#HTTP/\d\.\d\s(.*)?\R#", $http_message, $status);

        if (!empty($status)) {
            return $status[1];
        }

        return null;
    }

    /**
     * Return associative array containing HTTP headers when supplied with raw HTTP message
     *
     * @param string $http_message
     * @return null|array
     */
    private function getHttpHeaders(&$http_message)
    {
        preg_match_all("/([A-Za-z0-9-_]+):\s?(.*?)$/m", $http_message, $headers);

        if (!empty($headers)) {
            return array_combine($headers[1], $headers[2]);
        }

        return null;
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
     * Return HTTP status message
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
