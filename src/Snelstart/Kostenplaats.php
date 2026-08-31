<?php

namespace Krabo\SnelstartBundle\Snelstart;

use Krabo\SnelstartBundle\Factory;
use SnelstartPHP\Connector\V2\GrootboekConnector;
use SnelstartPHP\Connector\V2\KostenplaatsConnector;
use SnelstartPHP\Secure\ConnectionInterface;

class Kostenplaats {

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var KostenplaatsConnector
     */
    private $kostenplaatsConnector;

    /**
     * @var null|int
     */
    private $kostenplaatsNummer = null;


    /**
     * Kostenplaats constructor.
     * @param Factory $factory
     * @param null|int $kostenplaatsNummer
     */
    public function __construct(Factory $factory, $kostenplaatsNummer = null) {
        $this->factory = $factory;
        if ($kostenplaatsNummer) {
            $this->kostenplaatsNummer = $kostenplaatsNummer;
        }
    }

    /**
     * @return \SnelstartPHP\Model\V2\Kostenplaats|null
     */
    public function getKostenplaats() {
        if ($this->kostenplaatsNummer) {
            return $this->findKostenplaats($this->kostenplaatsNummer);
        }
        return null;
    }

    /**
     * @return KostenplaatsConnector
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getKostenPlaatsConnector() {
        if (!$this->kostenplaatsConnector) {
            $this->kostenplaatsConnector = new KostenplaatsConnector($this->factory->getConnection());
        }
        return $this->kostenplaatsConnector;
    }

    /**
     * @param $nummer
     * @return \SnelstartPHP\Model\V2\Kostenplaats|null
     */
    public function findKostenplaats($nummer) {
        static $kosten = null;
        static $cachedKostenplaats = [];

        if (isset($cachedKostenplaats[$nummer])) {
            return $cachedKostenplaats[$nummer];
        }

        $kostenplaatsen = $this->getKostenPlaatsConnector()->findAll();
        foreach($kostenplaatsen as $kostenplaats) {
            $cachedKostenplaats[$kostenplaats->getNummer()] = $kostenplaats;
        }

        if (isset($cachedKostenplaats[$nummer])) {
            return $cachedKostenplaats[$nummer];
        }

        return null;
    }

}
