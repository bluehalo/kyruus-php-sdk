<?php

namespace Asymmetrik\Kyruus\Test;

use PHPUnit\Framework\TestCase;
use Asymmetrik\Kyruus\SDK\Client;

class SDKClientTest extends TestCase {

    /**
     * @test
     */
    public function itShouldNotConnectOnCreationTest(){
        $this->assertInstanceOf(Client::class, new Client('root', 'user', 'pass', 'org'));
    }
}

