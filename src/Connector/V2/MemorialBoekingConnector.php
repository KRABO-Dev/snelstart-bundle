<?php
/**
 * @author  IntoWebDevelopment <info@intowebdevelopment.nl>
 * @project SnelstartApiPHP
 * @deprecated
 */

namespace Krabo\SnelstartBundle\Connector\V2;

use Krabo\SnelstartBundle\Mapper\V2\MemorialBoekingMapper;
use Krabo\SnelstartBundle\Model\V2\Memorialboeking;
use Krabo\SnelstartBundle\Request\V2\MemorialBoekingRequest;
use SnelstartPHP\Connector\BaseConnector;
use SnelstartPHP\Exception\PreValidationException;

final class MemorialBoekingConnector extends BaseConnector
{
    public function addMemorialBoeking(Memorialboeking $boeking): Memorialboeking
    {
        if ($boeking->getId() !== null) {
            throw PreValidationException::unexpectedIdException();
        }

        $boeking->assertInBalance();
        $boekingMapper = new MemorialBoekingMapper();
        $boekingRequest = new MemorialBoekingRequest();
        return $boekingMapper->addMemorialboeking($this->connection->doRequest($boekingRequest->addMemorialBoeking($boeking)));
    }
}