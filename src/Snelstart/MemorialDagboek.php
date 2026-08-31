<?php

namespace Krabo\SnelstartBundle\Snelstart;

use GuzzleHttp\Psr7\Request;
use Krabo\SnelstartBundle\Factory;

class MemorialDagboek {

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Grootboek
     */
    private $grootboek;

    private $dagboeken = [];


    public function __construct(Factory $factory, Grootboek $grootboek) {
        $this->factory = $factory;
        $this->grootboek = $grootboek;
    }

    /**
     * @return array
     */
    public function getDagboek($grootboekRekeningNummer) {
        if (!$this->dagboeken[$grootboekRekeningNummer]) {
            $grootboekRekening = $this->grootboek->findGrootboekRekening($grootboekRekeningNummer);
            $dagboeken = $this->factory->getConnection()->doRequest(new Request("GET", "dagboeken"));
            $dagboeken = json_decode($dagboeken->getBody()->getContents(), true);
            foreach ($dagboeken as $dagboek) {
                if ($dagboek['omschrijving'] == $grootboekRekening->getOmschrijving() && $dagboek['nummer'] == $grootboekRekening->getNummer()) {
                    $this->dagboeken[$grootboekRekeningNummer] = array(
                        'id' => $dagboek['id'],
                        'uri' => $dagboek['uri'],
                    );
                    break;
                }
            }
        }
        return $this->dagboeken[$grootboekRekeningNummer];
    }



}
