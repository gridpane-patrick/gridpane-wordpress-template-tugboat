<?php

require_once 'class-moyasar-http-response.php';
require_once 'class-moyasar-connection-exception.php';
require_once 'class-moyasar-http-exception.php';
require_once 'class-moyasar-http-client-exception.php';
require_once 'class-moyasar-http-server-exception.php';

class Moyasar_Quick_Http
{
    private $curl_handler;
    private $disposed = false;

    // Config
    private $headers = array();

    // Runtime
    public $cert_tmp_file;
    private $pk_tmp_file;

    public static function make()
    {
        return new static();
    }

    public function __construct()
    {
        global $wp_version;

        $this->curl_handler = curl_init(null);
        $this->headers['User-Agent'] = 'Moyasar Http; Woocommerce v' . MOYASAR_PAYMENT_VERSION . '; Wordpress v' . $wp_version;

        // Default Configurations
        curl_setopt($this->curl_handler, CURLOPT_TIMEOUT, 25);
    }

    public function __destruct()
    {
        $this->dispose();
    }

    public function cert($cert, $pk)
    {
        if (!$cert || !$pk) {
            throw new InvalidArgumentException('Client certificate is required');
        }

        $this->cert_tmp_file = tempnam('/tmp', 'ap-cert-');
        $this->pk_tmp_file = tempnam('/tmp', 'ap-pk-');

        file_put_contents($this->cert_tmp_file, $cert);
        file_put_contents($this->pk_tmp_file, $pk);

        curl_setopt($this->curl_handler, CURLOPT_SSLCERT, $this->cert_tmp_file);
        curl_setopt($this->curl_handler, CURLOPT_SSLKEY, $this->pk_tmp_file);

        return $this;
    }

    public function cert_passphrase($passphrase)
    {
        if (! empty($passphrase)) {
            curl_setopt($this->curl_handler, CURLOPT_SSLKEYPASSWD, $passphrase);
        }

        return $this;
    }

    public function basic_auth($username, $password = null)
    {
        $this->headers['Authorization'] = 'Basic ' . base64_encode("$username:$password");

        return $this;
    }

    public function set_headers($headers)
    {
        if (!is_array($headers)) {
            throw new InvalidArgumentException('headers must be an array');
        }

        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function setOption($option, $value)
    {
        curl_setopt($this->curl_handler, $option, $value);
        return $this;
    }

    public function request($method, $url, $data = array())
    {
        if ($this->disposed) {
            throw new Exception('Instance is in unusable state, please create a new one');
        }

        $is_json = is_array($data);
        $method = trim(strtoupper($method));

        if ($is_json) {
            $this->headers['Content-Type'] = 'application/json';
        }

        if ($is_json && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $data = json_encode($data);
            curl_setopt($this->curl_handler, CURLOPT_POSTFIELDS, $data);
        }

        if (in_array($method, array('GET', 'HEAD'))) {
            $url = $url . $this->encode_url_params($data);
        }

        $this
            ->setOption(CURLOPT_URL, $url)
            ->setOption(CURLOPT_CUSTOMREQUEST, $method)
            ->setOption(CURLOPT_HTTPHEADER, $this->build_headers())
            ->setOption(CURLOPT_RETURNTRANSFER, true)
            ->setOption(CURLOPT_HEADER, true);

        $raw_response = curl_exec($this->curl_handler);

        if ($error = curl_error($this->curl_handler)) {
            throw new Moyasar_Connection_Exception('HTTP Error: ' . $error . ', ' . curl_errno($this->curl_handler));
        }

        $status = curl_getinfo($this->curl_handler, CURLINFO_RESPONSE_CODE);
        $header_size = curl_getinfo($this->curl_handler, CURLINFO_HEADER_SIZE);
        $headers = $this->parse_headers(substr($raw_response, 0, $header_size));
        $body = substr($raw_response, $header_size);
        $response = new Moyasar_Http_Response($status, $headers, $body);

        $this->dispose();

        if ($response->isServerError()) {
            throw new Moyasar_Http_Server_Exception('Server Error', $response);
        }

        if ($response->isClientError()) {
            throw new Moyasar_Http_Client_Exception('Client Error', $response);
        }

        return $response;
    }

    public function get($url, $params = array())
    {
        return $this->request('GET', $url, $params);
    }

    public function post($url, $data = array())
    {
        return $this->request('POST', $url, $data);
    }

    public function put($url, $data = array())
    {
        return $this->request('PUT', $url, $data);
    }

    private function encode_url_params($params = [])
    {
        if (!is_array($params) || count($params) == 0) {
            return '';
        }

        $encoded = '?';

        foreach ($params as $key => $value) {
            $encoded .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        return rtrim($encoded, '&');
    }

    private function build_headers()
    {
        $raw = array();

        foreach ($this->headers as $key => $value) {
            $raw[] = $key . ': ' . $value;
        }

        return $raw;
    }

    private function parse_headers($raw)
    {
        $headers = array();

        foreach (explode("\r\n", $raw) as $line) {
            $line = explode(':', $line);

            if (count($line) < 2) {
                continue;
            }

            $headers[strtolower(trim($line[0]))] = trim($line[1]);
        }

        return $headers;
    }

    private function dispose()
    {
        if ($this->disposed) {
            return;
        }

        $this->disposed = true;

        // Close CURL Handler
        if (is_resource($this->curl_handler)) {
            curl_close($this->curl_handler);
        }

        // Clean Certs
        if (!$this->cert_tmp_file && file_exists($this->cert_tmp_file)) {
            unlink($this->cert_tmp_file);
        }

        if (!$this->pk_tmp_file && file_exists($this->pk_tmp_file)) {
            unlink($this->pk_tmp_file);
        }
    }
}
