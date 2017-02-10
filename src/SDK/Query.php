<?php


namespace Asymmetrik\Kyruus\SDK;


class Query
{
    /**
     * @var array
     */
    protected $_query;

    public function __construct(){
        $this->_query = [];
    }

    /**
     * @return Query
     */
    public function providers(){
        return $this->parameter('providers');
    }

    /**
     * Results per page
     * @param $amt int|string Number of results per page, recommended <=50
     * @return Query
     */
    public function perPage($amt){
        return $this->parameter('per_page', $amt);
    }

    /**
     * @param $page
     * @return Query
     */
    public function page($page){
        return $this->parameter('page', $page);
    }

    /**
     * Get facet count on facet field
     * @param $facet string String representing json object facet
     * @return Query
     */
    public function facet($facet){
        return $this->parameter('facet', $facet);
    }

    /**
     * @param $name string
     * @return Query
     */
    public function name($name){
        return $this->parameter('name', $name);
    }

    /**
     * @param $prefix string
     * @return Query
     */
    public function name_prefix($prefix){
        return $this->parameter('name_prefix', $prefix);
    }

    /**
     * @param $sort string Sort on key
     * @return Query
     */
    public function sort($sort){
        return $this->parameter('sort', $sort);
    }

    /**
     * Generic parameter
     * @param $param string Parameter name
     * @param $value null|string Parameter value
     * @return Query
     */
    public function parameter($param, $value = null){
        $this->_query[] = $param.($value ? '='.$value : '');
        return $this;
    }

    /**
     * Guarantee consistent search ordering
     * @param $seed string
     * @return Query
     */
    public function shuffle_seed($seed){
        return $this->parameter('shuffle_seed', $seed);
    }

    /**
     * @param $filter string
     * @return Query
     */
    public function filter($filter){
        return $this->parameter('filter', $filter);
    }

    /**
     * Sort by availability
     * @return Query
     */
    public function sortAvailability(){
        $this->sort('availability_total');
        return $this->parameter('availability_format', 1);
    }

    /**
     * @param $city string
     * @param $state string
     * @return Query
     */
    public function location($city, $state='MD'){
        return $this->parameter('location', $city.','.strtoupper($state));
    }

    /**
     * @param $miles int|string Number of miles
     * @return Query
     */
    public function distance($miles){
        return $this->parameter('distance', $miles);
    }

    /**
     * Compile query
     * @return string
     */
    public function compile(){
        $query = '';
        foreach($this->_query as $idx => $part){
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
        $this->_query = [];
        return $query;
    }
}