<?php


namespace Asymmetrik\Kyruus\Http;

use \Exception;
use GuzzleHttp\Client;
use Spatie\Regex\Regex;
use Asymmetrik\Kyruus\Exception\OAuthException;
use Asymmetrik\Kyruus\Exception\RequestException;
use League\OAuth2\Client\Provider\GenericProvider;

class RequestCoordinator
{
    /**
     * @var GenericProvider
     */
    private $oauth;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var \League\OAuth2\Client\Token\AccessToken
     */
    private $_token;

    /**
     * @param $url
     * @return string
     */
    protected function forceHttps($url){
        return 'https://'.Regex::replace('/^(https?:)?\/\//i', '', $url);
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    protected function getToken(){
        return $this->oauth->getAccessToken('client_credentials');
    }

    /**
     * @param $request
     * @return string
     */
    protected function generateApiUrl($request){
        return 'https://'.$this->root_url.'/pm/v8/'.$this->endpoint.'/'.ltrim('/',$request);
    }

    /**
     * @param $user Username
     * @param $password Password
     * @param $root OAuth Root
     * @param $endpoint Organization source
     * @return GenericProvider
     */
    protected function generateProvider($user, $password, $root, $endpoint){
        return new GenericProvider([
            'cliendId' => $user,
            'clientSecret' => $password,
            'urlAuthorize' => $root.'/oauth2/token',
        ]);

        $this->endpoint = $endpoint;
    }

    /**
     * RequestCoordinator constructor.
     * @param $oauthRoot
     * @param $username
     * @param $password
     */
    public function __construct($oauthRoot, $username, $password)
    {
        $this->oauth = $this->generateProvider($username, $password, $oauthRoot);
    }

    /**
     * @param $method
     * @param $url
     * @param null $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws OAuthException
     * @throws RequestException
     */
    protected function _wrappedRequest($method, $url, $data=null){
        if(!$this->_token) {
            try {
                $this->_token = $this->getToken();
            } catch (Exception $e){
                throw new OAuthException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $headers = [
            'json' => $data,
            'Authorization' => 'Bearer '.$this->_token
        ];

        if(is_null($data))
            unset($headers['json']);

        try {
            return (new Client())->request($method, $this->generateApiUrl($url), $headers);
        } catch(Exception $e){
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param $url
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function get($url){
        return $this->_wrappedRequest('GET', $this->generateApiUrl($url));
    }

    /**
     * @param $url
     * @param $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function post($url, $data){
        return $this->_wrappedRequest('POST', $this->generateApiUrl($url), $data);
    }

    /**
     * @param $url
     * @param $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function put($url, $data){
        return $this->_wrappedRequest('PUT', $this->generateApiUrl($url), $data);
    }

    /**
     * @param $url
     * @param $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function patch($url, $data){
        return $this->_wrappedRequest('PATCH', $this->generateApiUrl($url), $data);
    }

    /**
     * @param $url
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function delete($url){
        return $this->_wrappedRequest('DELETE', $this->generateApiUrl($url));
    }
}