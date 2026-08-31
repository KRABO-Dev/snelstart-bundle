<?php

namespace Krabo\SnelstartBundle;

use SnelstartPHP\Secure\AccessTokenConnection;
use SnelstartPHP\Secure\ApiSubscriptionKey;
use SnelstartPHP\Secure\BearerToken\ClientKeyBearerToken;
use SnelstartPHP\Secure\V2Connector;

class Factory {

    /**
     * @var String
     */
    private $client_key;

    /**
     * @var String
     */
    private $primary_key;

    /**
     * @var String
     */
    private $secondary_key;

    /**
     * @var V2Connector
     */
    private $connection;


    /**
     * Factory constructor.
     *
     * @param String $client_key
     * @param String $primary_key
     * @param String $secondary_key
     */
    public function __construct(String $client_key, String $primary_key, String $secondary_key) {
        $this->client_key = $client_key;
        $this->primary_key = $primary_key;
        $this->secondary_key = $secondary_key;
    }

    /**
     * @return V2Connector
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConnection() {
        if (!$this->connection) {
            $bearerToken = new ClientKeyBearerToken($this->client_key);
            $apiSubscriptionKeys = new ApiSubscriptionKey($this->primary_key, $this->secondary_key);
            $accessTokenConnection = new AccessTokenConnection($bearerToken);
            $token = $accessTokenConnection->getToken();
            $this->connection = new V2Connector($apiSubscriptionKeys, $token);
        }
        return $this->connection;
    }

}
