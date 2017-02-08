<?php

namespace Asymmetrik\Kyruus\Test;

use Asymmetrik\Kyruus\SDK\Client;
use ArgumentCountError;

class SDKClientTest extends TestCase {

    /**
     * @test
     * @expectedException ArgumentCountError
     */
    public function emptyCreationTest(){
        new Client();
    }
}

