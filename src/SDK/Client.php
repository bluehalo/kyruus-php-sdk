<?php

namespace Asymmetrik\Kyruus\SDK;

use Asymmetrik\Kyruus\Http\RequestCoordinator;
use Doctrine\Common\Collections\ArrayCollection;

class Client {
    /**
     * @var RequestCoordinator
     */
    private $client;

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

    public function get_locations() {
        $response = $this->search('/providers?per_page=1');

        if ( $response->getStatusCode() != 200) {
            return [];
        }

        $facets = new ArrayCollection(json_decode($response->getBody())->facets);

        $locations = $facets->filter(function($val){
            return $val->field === 'locations.name';
        });

        return is_null($locations) ? [] : $locations;
    }
}