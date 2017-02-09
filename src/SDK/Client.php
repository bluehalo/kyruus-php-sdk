<?php

namespace Asymmetrik\Kyruus\SDK;

use Asymmetrik\Kyruus\Http\RequestCoordinator;
use Doctrine\Common\Collections\ArrayCollection;
use Asymmetrik\Kyruus\Exception\RequestException;

class Client {
    /**
     * @var RequestCoordinator
     */
    private $client;

    private $query = [];

    /**
     * Kyruus API version
     */
    const VERSION = 'v8';

    public function __construct($oauthRoot, $user_name, $password) {
        $this->client = new RequestCoordinator($oauthRoot, $user_name, $password);
        $this->client->setEndpoint('/pm/'.self::VERSION.'/');
    }

    public function search($url) {
        return $this->client->get($url);
    }

    public function providers(){
        $this->query[] = 'providers';
        return $this;
    }

    public function perPage($amt){
        $this->query[] = 'per_page='.$amt;
        return $this;
    }

    public function compile(){
        $query = '';
        foreach($this->query as $idx => $part){
            switch($idx){
                case 0:
                    $query = $part;
                    break;
                case 1:
                    $query .= '?'.$part;
                    break;
                default:
                    $query .= '&'.$part;
            }
        }

        return $query;
    }

    public function getLocations() {
        $response = $this->search($this->providers()->perPage(1)->compile());

        if ( $response->getStatusCode() != 200)
            throw new RequestException($response->getReasonPhrase(), $response->getStatusCode());


        $facets = new ArrayCollection(json_decode($response->getBody())->facets);

        $locations = $facets->filter(function($val){
            return $val->field === 'locations.name';
        });

        return $locations;
    }

}