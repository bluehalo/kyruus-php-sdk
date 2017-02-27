<?php

namespace Asymmetrik\Kyruus\SDK;

use Asymmetrik\Kyruus\Http\Coordinator;
use Doctrine\Common\Collections\ArrayCollection;
use Asymmetrik\Kyruus\Exception\RequestException;

class Client {
    /**
     * @var RequestCoordinator
     */
    private $client;

    /**
     * @var Query
     */
    private $query = null;

    /**
     * Kyruus API version
     */
    const VERSION = 'v8';

    /**
     * Client constructor.
     * @param Coordinator $coordinator
     * @param $organization
     */
    public function __construct(Coordinator $coordinator, $organization) {
        $this->client = $coordinator;
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
     * @return QueryBuilder
     */
    public function providers(){
        return $this->builder()->providers();
    }

    /**
     * Get direct builder instance
     * @return QueryBuilder
     */
    public function builder(){
        return (new QueryBuilder($this));
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
            $response = $this->search($closure);

        if ($response->getStatusCode() >= 300)
            throw new RequestException($response->getReasonPhrase(), $response->getStatusCode());

        return json_decode($response->getBody());
    }

    /**
     * @param $query Callable|string
     * @return mixed
     */
    public function runQuery($query){
       return $this->_wrappedQuery($query);
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