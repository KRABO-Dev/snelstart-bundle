<?php

namespace Krabo\SnelstartBundle\Request\V2;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Krabo\SnelstartBundle\Model\V2\Memorialboeking;
use SnelstartPHP\Request\BaseRequest;

final class MemorialBoekingRequest extends BaseRequest
{
    public function addMemorialBoeking(Memorialboeking $boeking): RequestInterface
    {
        //echo json_encode($this->prepareAddOrEditRequestForSerialization($boeking), JSON_PRETTY_PRINT);exit();
        return new Request("POST", "memoriaalboekingen", [
            "Content-Type"  =>  "application/json"
        ], \GuzzleHttp\json_encode($this->prepareAddOrEditRequestForSerialization($boeking)));
    }
}