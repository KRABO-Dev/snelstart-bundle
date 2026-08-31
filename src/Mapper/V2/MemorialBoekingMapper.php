<?php
/**
 * @author  IntoWebDevelopment <info@intowebdevelopment.nl>
 * @project SnelstartApiPHP
 * @deprecated
 */

namespace Krabo\SnelstartBundle\Mapper\V2;

use Krabo\SnelstartBundle\Model\V2\Memorialboeking;
use Krabo\SnelstartBundle\Model\V2\MemorialBoekingsregel;
use SnelstartPHP\Model\Type\BtwSoort;
use function array_map;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use SnelstartPHP\Mapper\AbstractMapper;
use SnelstartPHP\Model\Kostenplaats;
use SnelstartPHP\Model\V2 as Model;

final class MemorialBoekingMapper extends AbstractMapper
{

    public function addMemorialboeking(ResponseInterface $response): Memorialboeking
    {
        $this->setResponseData($response);
        return $this->mapBoekingResult(new Memorialboeking());
    }

    protected function mapBoekingResult(Memorialboeking $boeking, array $data = []): Memorialboeking
    {
        $data = empty($data) ? $this->responseData : $data;

        /**
         * @var Memorialboeking $boeking
         */
        $boeking = $this->mapArrayDataToModel($boeking, $data);

        if (isset($data["modifiedOn"])) {
            $boeking->setModifiedOn(new \DateTimeImmutable($data["modifiedOn"]));
        }

        if (isset($data["factuurDatum"])) {
            $boeking->setFactuurdatum(new \DateTimeImmutable($data["factuurDatum"]));
        }

        if (isset($data["vervalDatum"])) {
            $boeking->setVervaldatum(new \DateTimeImmutable($data["vervalDatum"]));
        }

        if (isset($data["factuurBedrag"])) {
            $boeking->setFactuurbedrag($this->getMoney($data["factuurBedrag"]));
        }

        if (isset($data["memoriaalBoekingsRegels"])) {
            $boeking->setBoekingsregels($data['memoriaalBoekingsRegels']);
        }

        if (isset($data['inkoopboekingBoekingsRegels'])) {
            $boeking->setInkoopboekingBoekingsRegels($data['inkoopboekingBoekingsRegels']);
        }

        if (isset($data['verkoopboekingBoekingsRegels'])) {
            $boeking->setVerkoopboekingBoekingsRegels($data['verkoopboekingBoekingsRegels']);
        }

        return $boeking;
    }
}