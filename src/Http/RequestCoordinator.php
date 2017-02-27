<?php


namespace Asymmetrik\Kyruus\Http;

use \Exception;
use GuzzleHttp\Client;
use Spatie\Regex\Regex;
use Asymmetrik\Kyruus\Exception\OAuthException;
use Asymmetrik\Kyruus\Exception\RequestException;
use League\OAuth2\Client\Provider\GenericProvider;

class RequestCoordinator implements Coordinator
{
    /**
     * @var GenericProvider
     */
    private $oauth;

    /**
     * @var string
     */
    private $endpoint = '';

    /**
     * @var string
     */
    private $root = '';

    /**
     * @var string
     */
    private $organization = '';

    /**
     * @var \League\OAuth2\Client\Token\AccessToken
     */
    private $_token;

    /**
     * RequestCoordinator constructor.
     * @param $oauthRoot
     * @param $username
     * @param $password
     */
    public function __construct($oauthRoot, $username, $password)
    {
        $this->oauth = $this->generateProvider($username, $password, $oauthRoot);
        $this->root = $oauthRoot;
    }

    /**
     * @param $url
     * @return string
     */
    protected function forceHttps($url){
        return Regex::replace('/^(https?:)?\/\//i', 'https://', $url)->result();
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
        return $this->forceHttps($this->root.$this->endpoint.$this->organization.'/'.ltrim($request, '/'));
    }

    /**
     * @param $user Username
     * @param $password Password
     * @param $root OAuth Root
     * @return GenericProvider
     */
    protected function generateProvider($user, $password, $root){
        return new GenericProvider([
            'clientId' => $user,
            'clientSecret' => $password,
            'urlAuthorize' => $root.'/oauth2/token',
            'urlAccessToken' => $root.'/oauth2/token',
            'urlResourceOwnerDetails' => ''
        ]);
    }

    /**
     * @param $organization
     */
    public function setOrganization($organization){
        $this->organization = $organization;
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

        $payload = [
            'json' => $data,
            'headers' => [
                'Authorization' => 'Bearer '.$this->_token->getToken()
            ]
        ];

        foreach($payload as $key => $header)
            if(is_null($header)) unset($payload[$key]);

        try {
            return (new Client())->request($method, $url, $payload);
        } catch(Exception $e){
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function setEndpoint($endpoint){
        $this->endpoint = $endpoint;
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