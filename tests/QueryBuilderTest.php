<?php

namespace Asymmetrik\Kyruus\Test;

use Asymmetrik\Kyruus\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Asymmetrik\Kyruus\SDK\QueryBuilder;

class QueryBuilderTest extends TestCase {

    private $funcs = [
        'per_page' => 1,
        'page' => 1,
        'facet' => 'faceted',
        'name' => 'Smith',
        'name_prefix' => 'Mr',
        'sort' => 'no',
        'shuffle_seed' => 9001,
        'distance' => 5,
        'location' => ['boston', 'ma']
    ];

    /**
     * @test
     */
    public function itShouldCreateQueryBuilder(){
        $this->assertInstanceOf(QueryBuilder::class, new QueryBuilder());
    }

    /**
     * @test
     */
    public function itShouldAddArbitraryQueryParameter(){
        $builder = new QueryBuilder();

        $builder->parameter('woo', 'man');
        $this->assertRegExp('/woo=man/', $builder->compile());
    }

    /**
     * @test
     */
    public function itShouldAddArbitrayTopLevelParamter(){
        $builder = new QueryBuilder();

        $builder->parameter('thing');
        $compiled = $builder->compile();

        $this->assertRegExp('/thing/', $compiled);
        $this->assertFalse(strpos($compiled, '='));
    }

    /**
     * @test
     */
    public function itShouldPassAllAPICalls(){
        $builder = new QueryBuilder();

        foreach($this->funcs as $func => $arg){
            if(is_array($arg) || is_null($arg)) continue;

            $builder->{$func}($arg);

            $this->assertRegExp('/'.$func.'='.$arg.'/', $builder->compile());
        }
    }

    /**
     * @test
     */
    public function itShouldHandleLocationsByMarylandCity(){
        $builder = new QueryBuilder();

        $builder->location('Silver Spring');
        $this->assertRegExp('/Silver Spring,MD/', $builder->compile());
    }

    /**
     * @test
     */
    public function itShouldHandleArbitraryLocations(){
        $builder = new QueryBuilder();

        $builder->location('boston', 'MA');
        $this->assertRegExp('/boston,MA/', $builder->compile());
    }

    /**
     * @test
     */
    public function itShouldCapitalizeLocationState(){
        $builder = new QueryBuilder();

        $builder->location('boston', 'ma');
        $this->assertRegExp('/MA/', $builder->compile());
    }

    /**
     * @test
     */
    public function itShouldCompileSingleQuery(){
        $builder = new QueryBuilder();

        $builder->providers();
        $this->assertRegExp('/^providers$/', $builder->compile());
    }

    /**
     * @test
     */
    public function itShouldCompileTwoQueries(){
        $builder = new QueryBuilder();

        $builder->providers();
        $builder->name('jennifer');

        $this->assertRegExp('/^providers\?name=jennifer$/', $builder->compile());
    }

    /**
     * @test
     */
    public function itShouldCompileThreeQueries(){
        $builder = new QueryBuilder();

        $builder->providers();
        $builder->name('jennifer');
        $builder->per_page(5);

        $this->assertRegExp('/^providers\?name=jennifer&per_page=5$/', $builder->compile());
    }

    /**
     * @test
     */
    public function itShouldAllowChainingQueries(){
        $builder = new QueryBuilder();
        $chained = new QueryBuilder();

        $builder->providers();
        $builder->name('jennifer');
        $builder->per_page(5);

        $chained->providers()->name('jennifer')->per_page(5);

        $this->assertEquals($builder, $chained);
    }

    /**
     * @test
     */
    public function itShouldHaveAllFunctionsChainable(){
        $builder = new QueryBuilder();

        foreach($this->funcs as $func => $args){
            if(is_array($args)){
                $builder = call_user_func_array([$builder, $func], $args);
            }
            else if(is_null($args))
                $builder = $builder->{$func}();
            else
                $builder = $builder->{$func}($args);
        }

        $compiled = $builder->compile();
        foreach(array_keys($this->funcs) as $part){
            $this->assertRegExp('/'.$part.'/', $compiled);
        }
    }

    /**
     * @test
     */
    public function itShouldSortingAvailability(){
        $builder = new QueryBuilder();

        $builder->sortAvailability();

        $compiled = $builder->compile();

        $this->assertRegExp('/sort=availability/', $compiled);
        $this->assertRegExp('/availability_format=1/', $compiled);
    }

    /**
     * @test
     * @expectedException Asymmetrik\Kyruus\Exception\RequestException
     */
    public function itShouldBreakIfNoClientGiven(){
        $builder = new QueryBuilder();

        $builder->providers();
        $builder->get();
    }
}

