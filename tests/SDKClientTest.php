<?php

namespace Asymmetrik\Kyruus\Test;

use Asymmetrik\Kyruus\Http\Coordinator;
use Asymmetrik\Kyruus\Http\RequestCoordinator;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Asymmetrik\Kyruus\SDK\Client;
use Asymmetrik\Kyruus\SDK\QueryBuilder;

use \ReflectionClass;

class SDKClientTest extends TestCase {

    /**
     * @var ReflectionClass
     */
    protected $reflector;

    public function setUp(){
        $this->reflector = new ReflectionClass(Client::class);
    }

    /**
     * @test
     */
    public function itShouldNotConnectOnCreationTest(){
        $this->assertInstanceOf(Client::class, new Client(new RequestCoordinator('root', 'user', 'pass'), 'org'));
    }

    /**
     * @test
     */
    public function itShouldCreateQueryBuilderWhenCallingProviders(){
        $client = $this->reflector->newInstanceWithoutConstructor();

        $this->assertInstanceOf(QueryBuilder::class, $client->providers());
    }

    /**
     * @test
     */
    public function itShouldTakeInAnyCoordinator(){
        $coordinator = new TestCoord('root', 'user', 'pass');

        $this->assertCount(1, $coordinator->events);

        new Client($coordinator, 'org');

        $this->assertCount(3, $coordinator->events);

        $events = ['create', 'setEndpoint', 'setOrganization'];

        foreach($coordinator->events as $event){
            $this->assertContains($event[0], $events);
        }
    }

    /**
     * @test
     */
    public function itShouldCallCoordinatorGetOnSearch(){
        $coordinator = new TestCoord('root', 'user', 'pass');
        $client = new Client($coordinator, 'org');

        $client->search('searchurl');

        $this->assertEquals(end($coordinator->events)[0], 'get');
        $this->assertEquals(end($coordinator->events)[1][0], 'searchurl');
    }

    /**
     * @test
     */
    public function itShouldGetAffiliations(){
        $coordinator = new TestCoord('root', 'user', 'pass');
        $client = new Client($coordinator, 'org');

        $data = ['facets' => [['name' => 'thing', 'count' => 2]]];
        $coordinator->responses[] = new Response($data, 200);

        $affiliations = $client->affiliations();

        $this->assertInstanceOf(ArrayCollection::class, $affiliations);
        $this->assertEquals(json_encode($data['facets']), json_encode($affiliations->toArray()));
    }

    /**
     * @test
     */
    public function itShouldGetLocations(){
        $coordinator = new TestCoord('root', 'user', 'pass');
        $client = new Client($coordinator, 'org');

        $data = ['facets' => [['name' => 'thing', 'count' => 2]]];
        $coordinator->responses[] = new Response($data, 200);

        $locations = $client->locations();

        $this->assertInstanceOf(ArrayCollection::class, $locations);
        $this->assertEquals(json_encode($data['facets']), json_encode($locations->toArray()));
    }

    /**
     * @test
     */
    public function itShouldReturnAffiliationsAndLocations(){
        $coordinator = new TestCoord('root', 'user', 'pass');
        $client = new Client($coordinator, 'org');

        $data = ['facets' => [['name' => 'thing', 'count' => 2]]];
        $coordinator->responses[] = new Response($data, 200);
        $coordinator->responses[] = new Response($data, 200);

        $locations = $client->getLocations();

        $this->assertArrayHasKey('affiliations', $locations);
        $this->assertArrayHasKey('locations', $locations);
    }

    /**
     * @test
     * @expectedException Asymmetrik\Kyruus\Exception\RequestException
     */
    public function itShouldFailOnBadResponse(){
        $coordinator = new TestCoord('root', 'user', 'pass');
        $client = new Client($coordinator, 'org');

        $coordinator->responses[] = new Response('Test Error', random_int(300, 600));

        $client->affiliations();
    }

    /**
     * @test
     */
    public function itShouldSucceedOn200Response(){
        $coordinator = new TestCoord('root', 'user', 'pass');
        $client = new Client($coordinator, 'org');

        $data = ['facets' => [['name' => 'thing', 'count' => 2]]];
        $coordinator->responses[] = new Response($data, random_int(200, 299));

        $this->assertNotNull($client->affiliations());
    }
}

class Response{
    public $msg;
    public $code;

    public function __construct($msg, $code)
    {
        $this->msg = $msg;
        $this->code = $code;
    }

    public function getStatusCode(){
        return $this->code;
    }

    public function getReasonPhrase(){
        return $this->msg;
    }

    public function getBody(){
        return json_encode($this->msg);
    }
}

class TestCoord implements Coordinator {
    public $events = [];

    public $responses = [];

    /**
     * RequestCoordinator constructor.
     * @param $oauthRoot
     * @param $username
     * @param $password
     */
    public function __construct($oauthRoot, $username, $password)
    {
        $this->events[] = ['create', [$oauthRoot, $username, $password]];
    }

    public function setOrganization($organization){
        $this->events[] = ['setOrganization', [$organization]];
    }

    /**
     * @param $endpoint
     * @return mixed
     */
    public function setEndpoint($endpoint)
    {
        $this->events[] = ['setEndpoint', [$endpoint]];
    }

    /**
     * @param $url
     * @return mixed
     */
    public function get($url)
    {
        $this->events[] = ['get', [$url]];
        return array_pop($this->responses);
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function post($url, $data)
    {
        $this->events[] = ['post', [$url, $data]];
        return array_pop($this->responses);
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function put($url, $data)
    {
        $this->events[] = ['put', [$url, $data]];
        return array_pop($this->responses);
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function patch($url, $data)
    {
        $this->events[] = ['patch', [$url, $data]];
        return array_pop($this->responses);
    }

    /**
     * @param $url
     * @return mixed
     */
    public function delete($url)
    {
        $this->events[] = ['delete', [$url]];
        return array_pop($this->responses);
    }
}