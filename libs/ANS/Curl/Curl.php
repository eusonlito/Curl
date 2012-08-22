<?php
namespace ANS\Curl;

class Curl {
    private $connection;
    private $server = '';
    private $headers = false;
    private $response;
    private $info;
    private $json = false;
    private $compact = true;
    private $debug = false;

    private $Timer;
    private $Debug;

    public function init ($server)
    {
        $this->server = $server;
        $this->connection = curl_init();

        curl_setopt($this->connection, CURLOPT_REFERER, $this->server);
        curl_setopt($this->connection, CURLOPT_FAILONERROR, true);

        if (!ini_get('open_basedir') && (strtolower(ini_get('safe_mode')) !== 'on')) {
            curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, true);
        }

        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->connection, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->connection, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->connection, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($this->connection, CURLOPT_HTTPHEADER, array('Content-type: text/plain'));

        if ($this->headers) {
            curl_setopt($this->connection, CURLOPT_HEADER, true);
            curl_setopt($this->connection, CURLOPT_VERBOSE, true);
        }
    }

    public function setOption ($option, $value)
    {
        curl_setopt($this->connection, $option, $value);
    }

    public function setTimer ($Timer)
    {
        $this->Timer = &$Timer;
    }

    public function setCache ($Cache)
    {
        $this->Cache = $Cache;
    }

    public function setDebug ($Debug, $function)
    {
        if (is_object($Debug) && method_exists($Debug, $function)) {
            $Debug->function = $function;
            $this->Debug = $Debug;
        }
    }

    public function debug ($text, $trace = true)
    {
        if ($this->Debug) {
            $this->Debug->{$this->Debug->function}($text, $trace);
        }
    }

    public function setJson ($json)
    {
        $this->json = $json;
    }

    public function fullGet ($url)
    {
        $server = $this->server;
        $info = parse_url($url);

        $this->server = $info['scheme'].'://'.$info['host'];

        $response = $this->get($info['path'].(isset($info['query']) ? ('?'.$info['query']) : ''));

        $this->server = $server;

        return $response;
    }

    public function get ($url, $post = false, $cache = false)
    {
        $cache = (!$post && $this->Cache && $cache);

        if ($cache && $this->Cache->exists($url)) {
            return $this->Cache->get($url);
        }

        if ($this->Timer) {
            $this->Timer->mark('INI: Curl->get');
        }

        $remote = $this->server.$url;

        $this->debug('Connection to <strong>'.$remote.'</strong>');

        curl_setopt($this->connection, CURLOPT_URL, $remote);

        $this->response = curl_exec($this->connection);
        $this->info = curl_getinfo($this->connection);

        if (!$this->response) {
            return '';
        }

        if ($this->compact) {
            $html = preg_replace('/>\s+</', '><', str_replace(array("\n", "\r", "\t"), '', $this->response));
        } else {
            $html = $this->response;
        }

        if ($this->json) {
            $html = json_decode($html);
        }

        if ($cache) {
            $this->Cache->set($url, $html);
        }

        if ($this->Timer) {
            $this->Timer->mark('END: Curl->get');
        }

        return $html;
    }

    public function post ($url, $data)
    {
        curl_setopt($this->connection, CURLOPT_POST, true);
        curl_setopt($this->connection, CURLOPT_POSTFIELDS, (is_array($data) ? json_encode($data) : $data));

        $html = $this->get($url, true);

        curl_setopt($this->connection, CURLOPT_POST, false);

        return $html;
    }

    public function custom ($request, $url, $data = array())
    {
        curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, $request);

        if ($data) {
            curl_setopt($this->connection, CURLOPT_POSTFIELDS, (is_array($data) ? json_encode($data) : $data));
        }

        $html = $this->get($url, true);

        curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, false);

        return $html;
    }

    public function getInfo ()
    {
        return $this->info;
    }

    public function getResponse ()
    {
        return $this->response;
    }

    public function setCookie ($value)
    {
        curl_setopt($this->connection, CURLOPT_COOKIE, $value);
    }
}
