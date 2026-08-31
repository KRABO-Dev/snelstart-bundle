<?php

namespace Krabo\SnelstartBundle\Snelstart;

use GuzzleHttp\Psr7\Request;
use Krabo\SnelstartBundle\Factory;
use Ramsey\Uuid\Uuid;
use SnelstartPHP\Connector\V2\GrootboekConnector;
use SnelstartPHP\Secure\ConnectionInterface;

class Grootboek {

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var GrootboekConnector
     */
    private $grootBoekConnector;

    /**
     * @var \SnelstartPHP\Model\V2\Grootboek[]
     */
    private $grootBoekrekeningen = [];

    /**
     * @var \SnelstartPHP\Model\V2\Grootboek
     */
    private $euBTWRekening = null;

    public function __construct(Factory $factory) {
        $this->factory = $factory;
    }

    /**
     * @return GrootboekConnector
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConnector() {
        if (!$this->grootBoekConnector) {
            $this->grootBoekConnector = new GrootboekConnector($this->factory->getConnection());
        }
        return $this->grootBoekConnector;
    }

    /**
     * @param $nummer
     * @return \SnelstartPHP\Model\V2\Grootboek|null
     */
    public function findGrootboekRekening($nummer) {
        if (isset($this->grootBoekrekeningen[$nummer])) {
            return $this->grootBoekrekeningen[$nummer];
        }
        $grootboekConnector = $this->getConnector();
        $grootboekRekeningen = $grootboekConnector->findAll((new \SnelstartPHP\Request\ODataRequestData())->setFilter(["Nummer eq ".$nummer]));
        foreach($grootboekRekeningen as $grootboekRekening) {
            $this->grootBoekrekeningen[$grootboekRekening->getNummer()] = $grootboekRekening;
            break;
        }

        if (isset($this->grootBoekrekeningen[$nummer])) {
            return $this->grootBoekrekeningen[$nummer];
        }
        return null;
    }

    /**
     * @return \SnelstartPHP\Model\V2\Grootboek|null
     */
    public function getEUBtwGrootboekRekening() {
        if (!$this->euBTWRekening) {
            $companyInfo = $this->factory->getConnection()->doRequest(new Request("GET", "companyInfo"));
            $companyInfo = json_decode($companyInfo->getBody()->getContents(), true);
            $euBTWRekeningId = $companyInfo['buitenlandseBtwGrootboek']['id'];
            $this->euBTWRekening = $this->getConnector()->find(Uuid::fromString($euBTWRekeningId));

            if ($this->euBTWRekening) {
                $this->grootBoekrekeningen[$this->euBTWRekening->getNummer()] = $this->euBTWRekening;
                return $this->euBTWRekening;
            }
        }
        return $this->euBTWRekening;
    }



}
