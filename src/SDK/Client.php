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

    /**
     * Client constructor.
     * @param $oauthRoot
     * @param $user_name
     * @param $password
     * @param $organization
     */
    public function __construct($oauthRoot, $user_name, $password, $organization) {
        $this->client = new RequestCoordinator($oauthRoot, $user_name, $password);
        $this->client->setEndpoint('/pm/'.self::VERSION.'/');
        $this->client->setOrganization($organization);
    }

    /**
     * @param $url
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function search($url) {
        return $this->client->get($url);
    }

    /**
     * Providers query
     * @return $this
     */
    public function providers(){
        $this->query[] = 'providers';
        return $this;
    }

    /**
     * Results per page
     * @param $amt
     * @return $this
     */
    public function perPage($amt){
        $this->query[] = 'per_page='.$amt;
        return $this;
    }

    /**
     * Add facet parameter
     * @param $facet
     * @return $this
     */
    public function facet($facet){
        $this->query[] = 'facet='.$facet;
        return $this;
    }

    /**
     * Compile query
     * @return mixed|string
     */
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
        $this->query = [];
        return $query;
    }

    /**
     * Run query, string creates a search whereas a function passed just calls your implementation.
     * @param $closure Callable|string
     * @return mixed
     * @throws RequestException
     */
    protected function _wrappedQuery($closure){
        if(is_callable($closure))
            $response = $closure();
        else
            $response = $this->search($this->providers()->facet('network_affiliations.name')->compile());

        if ($response->getStatusCode() != 200)
            throw new RequestException($response->getReasonPhrase(), $response->getStatusCode());

        return json_decode($response->getBody());
    }

    /**
     * Get locations and network affiliations
     * @return array<ArrayCollection>
     * @throws RequestException
     */
    public function getLocations() {
        return [
            'affiliations' => $this->affiliations(),
            'locations' => $this->locations()
        ];
    }

    /**
     * @return ArrayCollection
     */
    public function affiliations(){
        return new ArrayCollection($this->_wrappedQuery($this->providers()->facet('network_affiliations.name')->compile())->facets);
    }

    /**
     * @return ArrayCollection
     */
    public function locations(){
        return new ArrayCollection($this->_wrappedQuery($this->providers()->facet('locations.name')->compile())->facets);
    }

}