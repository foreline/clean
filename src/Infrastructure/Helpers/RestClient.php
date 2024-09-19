<?php
declare(strict_types=1);

namespace Infrastructure\Helpers;

use Exception;

/**
 * Класс для вызова REST api
 */
class RestClient
{
    private string $url;
    //private Type $type;
    private string $token;

    /**
     *
     */
    public function __construct()
    {
    
    }

    /**
     * @return self
     */
    public function setAuthenticationMethod(): self
    {
        // @todo
        return $this;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody(string $body): self
    {
        // @fixme
        return $this;
    }
    
    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @param bool $debug
     * @return string|null
     * @throws Exception
     * @noinspection PhpTooManyParametersInspection
     */
    public static function callApi(string $url, string $method, array $params = [], bool $debug = false): ?string
    {
        $method = strtoupper($method);
    
        $curl = curl_init();
        
        // @fixme
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
        if (in_array($method, [/*'GET', */'POST', 'PUT', 'DELETE', 'PATCH'])) {
            $params = json_encode($params, JSON_THROW_ON_ERROR);
        }
    
        switch ($method) {
        
            case 'GET':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                $url .= '?' . http_build_query($params);
            
                break;
        
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            
                break;
        
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            
                break;
        
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            
                break;
        
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                if ($params) {
                    $url = sprintf("%s?%s", $url, http_build_query($params));
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                }
        }
    
        // Optional Authentication:
        //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
        $result = curl_exec($curl);
    
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
        // Check the HTTP Status code
        switch ($httpCode) {
            case 200:
                break;
            case 400: // Bad Request
                break;
            case 404:
            case 500:
            case 502:
            case 503:
            case 100:
                throw new Exception($httpCode . ': ' . curl_error($curl));
            default:
                throw new Exception($httpCode . ': ' . curl_error($curl));
        }
    
        curl_close($curl);
    
        return $result;
    }
    
    /**
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param bool $debug
     * @return string|null
     * @throws Exception
     * @noinspection PhpTooManyParametersInspection
     */
    public static function post(string $url, array $params = [], array $headers = [], bool $debug = false): ?string
    {
        return static::callApi($url, 'post', $params, $debug);
    }
    
    /**
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param bool $debug
     * @return ?array
     * @throws Exception
     * @noinspection PhpTooManyParametersInspection
     */
    public static function postJson(string $url, array $params = [], array $headers = [], bool $debug = false): ?array
    {
        return json_decode(static::post($url, $params, $headers, $debug), true);
    }
    
    /**
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param bool $debug
     * @return string|null
     * @throws Exception
     * @noinspection PhpTooManyParametersInspection
     */
    public static function get(string $url, array $params = [], array $headers = [], bool $debug = false): ?string
    {
        return static::callApi($url, 'get', $params, $debug);
    }
    
    /**
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param bool $debug
     * @return ?array
     * @throws Exception
     * @noinspection PhpTooManyParametersInspection
     */
    public static function getJson(string $url, array $params = [], array $headers = [], bool $debug = false): ?array
    {
        return json_decode(static::get($url, $params, $headers, $debug), true);
    }
}