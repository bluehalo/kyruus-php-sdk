<?php

namespace Asymmetrik\Kyruus\Http;

interface Coordinator
{
    /**
     * RequestCoordinator constructor.
     * @param $oauthRoot
     * @param $username
     * @param $password
     */
    public function __construct($oauthRoot, $username, $password);

    /**
     * @param $organization
     */
    public function setOrganization($organization);

    /**
     * @param $endpoint
     * @return mixed
     */
    public function setEndpoint($endpoint);

    /**
     * @param $url
     * @return mixed
     */
    public function get($url);

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function post($url, $data);

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function put($url, $data);

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function patch($url, $data);

    /**
     * @param $url
     * @return mixed
     */
    public function delete($url);
}