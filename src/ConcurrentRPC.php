<?php

/**
 * Class ConcurrentRPC
 *
 * A light PHP http concurrent RPC library class
 *
 * @author mombol
 * @contact mombol@163.com
 * @version v0.0.1
 */
class ConcurrentRPC
{
    /**
     * All connection open handler
     *
     * @var array
     */
    private $_fps = array();

    /**
     * All requests parameter
     *
     * @var array
     */
    private $_requests = array();

    /**
     * Url index
     *
     * @var int
     */
    private $_urlIndex = 0;

    /**
     * Put a url to be ready to get from the server
     *
     * @param $url
     * @param null $returnKey
     * @param array $data
     * @return $this
     */
    public function get($url, $returnKey = null, $data = array())
    {
        $this->push($this->buildRequest($url, $returnKey, $data));
        return $this;
    }

    /**
     * Put a url to be ready to post from the server
     *
     * @param $url
     * @param null $returnKey
     * @param array $data
     * @return $this
     */
    public function post($url, $returnKey = null, $data = array())
    {
        $this->push($this->buildRequest($url, $returnKey, $data, 'POST'));
        return $this;
    }

    /**
     * Receive the requests response content
     *
     * @return array
     */
    public function receive()
    {
        $result = array();
        foreach ($this->_requests as $request) {
            $this->send($request);
        }
        foreach ($this->_fps as $key => $fp) {
            $result[$key] = '';
            while (!feof($this->_fps[$key])) {
                $result[$key] .= fread($this->_fps[$key], 1024);
            }
            $result[$key] = $this->getBody($result[$key]);
            fclose($this->_fps[$key]);
        }
        return $result;
    }

    /**
     * Broadcast the requests but not receive any message
     */
    public function broadcast()
    {
        foreach ($this->_requests as $request) {
            $this->send($request, true);
        }
    }

    /**
     * Push a request parameter to the request attribute
     *
     * @param array $request
     */
    private function push($request = array())
    {
        if ($this->checkHttpUrlFormat($request['url'])) {
            $this->_requests[$request['returnKey']] = $request;
        } else {
            throw new InvalidArgumentException('Url: ' . $request['url'] . ' is not a regular http protocol address!');
        }
    }

    /**
     * Build a request parameter
     *
     * @param $url
     * @param null $returnKey
     * @param array $data
     * @param string $method
     * @return array
     */
    private function buildRequest($url, $returnKey = null, $data = array(), $method = 'GET')
    {
        $request = array();
        $request['url'] = $url;
        if (empty($returnKey)) {
            $request['returnKey'] = $this->getUrlIndex();
        } else {
            $request['returnKey'] = $returnKey;
        }
        $request['method'] = $method;
        $request['data'] = $data;
        return $request;
    }

    /**
     * Send a the request to the server
     *
     * @param $request
     * @param bool|false $close_immediate
     * @return bool
     */
    private function send($request, $close_immediate = false)
    {
        $method = isset($request['method']) ? strtoupper($request['method']) : "GET";
        $url_array = parse_url($request['url']);
        $port = isset($url_array['port']) ? $url_array['port'] : 80;
        $this->_fps[$request['returnKey']] = fsockopen($url_array['host'], $port, $errno, $errstr, 30);
        if (!$this->_fps[$request['returnKey']]) {
            return false;
        }
        $getPath = isset($url_array['path']) ? $url_array['path'] . (isset($url_array['query']) ? '?' . $url_array['query'] : '') : '/';
        $header = $method . " " . $getPath;
        $header .= " HTTP/1.0\r\n";
        $header .= "Host: " . $url_array['host'] . "\r\n";
        if (!empty($cookie)) {
            $_cookie = http_build_query($cookie);
            $cookie_str = "Cookie: " . base64_encode($_cookie) . "\r\n";
            $header .= $cookie_str;
        }
        $_post = '';
        if ($method === 'POST') {
            $_post = http_build_query($request['data']);
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= "Content-Length: " . strlen($_post) . "\r\n";
        }
        $header .= "Connection:Close\r\n";
        $header .= "\r\n";
        if (strlen($_post) > 0) {
            $header .= $_post . "\r\n";
        }
        fwrite($this->_fps[$request['returnKey']], $header);
        if ($close_immediate) {
            fclose($this->_fps[$request['returnKey']]);
        }
    }

    /**
     * Get the http response body content
     *
     * @param $data
     * @return mixed
     */
    private function getBody($data)
    {
        $data = $this->separate($data);
        return $data[1];
    }

    /**
     * Separate the http response content
     *
     * @param $data
     * @return array
     */
    private function separate($data)
    {
        $pos = strpos($data, "\r\n\r\n");
        return array(
            substr($data, 0, $pos),
            substr($data, $pos + 4)
        );
    }

    /**
     * Get the url index
     *
     * @return string
     */
    private function getUrlIndex()
    {
        return 'url_' . (++$this->_urlIndex);
    }

    /**
     * Check whether the url is a regular http address or not
     *
     * @param $url
     * @return int|bool
     */
    private function checkHttpUrlFormat($url)
    {
        return preg_match("/^http(s)?:\/\/[a-zA-Z0-9\.\-]+[\/\=\?%\-&_~`\@\[\]\'\:\+\!]*[^<>\"\"]*$/", $url);
    }
}
