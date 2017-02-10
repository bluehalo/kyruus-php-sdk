<?php


namespace Asymmetrik\Kyruus\SDK;


class Query
{
    protected $_query;
    public function __construct(){
        $this->_query = [];
    }

    public function providers(){
        $this->_query[] = 'providers';
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
        $this->_query[] = 'facet='.$facet;
        return $this;
    }

    /**
     * Compile query
     * @return mixed|string
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