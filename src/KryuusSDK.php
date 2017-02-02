<?php

namespace Asymmetrik;

use League\OAuth2\Client\Provider\GenericProvider;
use \GuzzleHttp\Client;

class KyruusSDK {
    private $client;
    private $root_url;
    private $source;
    private $user_name;
    private $password;
    private $token;
    private $given_token;

    const VERSION = 'v8';

    public function __construct($root, $source, $user_name, $password, $token = null) {
        $this->root_url = $root;
        $this->source = $source;
        $this->user_name = $user_name;
        $this->password = $password;

        $this->client = new Client();

        if ( !is_null($token) ) {
            $this->token = $token;
            $this->given_token = true;
            return;
        }

        $this->given_token = false;

        $client = new GenericProvider([
            'cliendId'          => $user_name,
            'clientSecret'      => $password,
            'urlAuthorize'      => $root.'/oauth2/token',
        ]);

        try {
            $this->token = $client->getAccessToken('client_credentials');
        } catch( Error $e ) {
            die(var_dump($e));
        }
    }

    public function search($method, $url) {
        $url = fix_protocol($url);

        $reponse = $this->client->request($method, $url);

        // If the token is not invalid/expired return the response
        if ( $reponse->getStatusCode() != 401 || $this->given_token) {
            return $reponse;
        }

        // The key is bad, generate a new one
        $client = new GenericProvider([
            'cliendId'          => $this->user_name,
            'clientSecret'      => $this->password,
            'urlAuthorize'      => $this->root_url.'/oauth2/token',
        ]);

        try {
            $this->token = $client->getAccessToken('client_credentials');
        } catch( Error $e ) {
            // TODO: Do something that makes sense
            die(var_dump($e));
        }

        return $this->client->request($method, $url);
    }


    public function get_token() {
        return $this->token;
    }

    public function get_locations() {
        $response = $this->search('GET', "https://$this->root_url/pm/v8/$this->source/providers?per_page=1");

        if ( $response->getStatusCode() != 200) {
            return [];
        }

        $facets = json_decode($response->getBody())->facets;

        $locations = find($facets, function($val, $key) {
            return $val->field === 'locations.name';
        });

        return is_null($locations) ? [] : $locations;
    }
}