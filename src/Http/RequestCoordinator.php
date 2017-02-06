<?php


namespace Asymmetrik\Kyruus\Http;

use Asymmetrik\Kyruus\Exception\OAuthException;
use Asymmetrik\Kyruus\Exception\RequestException;
use \Exception;
use GuzzleHttp\Client;
use Spatie\Regex\Regex;
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

    private $_token;

    /**
     * @param $url
     * @return string
     */
    protected function forceHttps($url){
        return 'https://'.Regex::replace('/^(https?:)?\/\//i', '', $url);
    }

    protected function getToken(){
        return $this->oauth->getAccessToken('client_credentials');
    }

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

    public function get($url){
        $this->_wrappedRequest('GET', $this->generateApiUrl($url));
    }

    public function post($url, $data){
        $this->_wrappedRequest('POST', $this->generateApiUrl($url), $data);
    }

    public function put($url, $data){
        $this->_wrappedRequest('PUT', $this->generateApiUrl($url), $data);
    }

    public function patch($url, $data){
        $this->_wrappedRequest('PATCH', $this->generateApiUrl($url), $data);
    }

    public function delete($url){
        $this->_wrappedRequest('DELETE', $this->generateApiUrl($url));
    }
}