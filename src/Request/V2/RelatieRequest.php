<?php
/**
 * Copyright (C) 2022  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Krabo\SnelstartBundle\Request\V2;


use GuzzleHttp\Psr7\Request;
use Money\Money;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\UuidInterface;
use SnelstartPHP\Exception\PreValidationException;
use SnelstartPHP\Model\BaseObject;
use SnelstartPHP\Model\SnelstartObject;
use SnelstartPHP\Model\V2 as Model;
use SnelstartPHP\Request\BaseRequest;
use SnelstartPHP\Request\ODataRequestDataInterface;

final class RelatieRequest extends BaseRequest
{
  public function findAll(ODataRequestDataInterface $ODataRequestData): RequestInterface
  {
    return new Request("GET", "relaties?" . $ODataRequestData->getHttpCompatibleQueryString());
  }

  public function find(UuidInterface $id): RequestInterface
  {
    return new Request("GET", "relaties/" . $id->toString());
  }

  public function findDoorlopendeIncassoMachtigingen(UuidInterface $id): RequestInterface
  {
    return new Request("GET", "relaties/" . $id->toString() . "/doorlopendeincassomachtigingen");
  }

  public function add(Model\Relatie $relatie): RequestInterface
  {
    return new Request("POST", "relaties", [
      "Content-Type"  =>  "application/json"
    ], \GuzzleHttp\json_encode($this->prepareAddOrEditRequestForSerialization($relatie)));
  }

  public function update(Model\Relatie $relatie): RequestInterface
  {
    if ($relatie->getId() === null) {
      throw PreValidationException::shouldHaveAnIdException();
    }

    return new Request("PUT", "relaties/" . $relatie->getId()->toString(), [
      "Content-Type"  =>  "application/json"
    ], \GuzzleHttp\json_encode($this->prepareAddOrEditRequestForSerialization($relatie)));
  }

  /**
   * Iterate over the Model objects and ask for the editable attributes. We will only serialize the editable fields
   * in this case.
   *
   * @param BaseObject $object
   * @param string[]   $editableAttributes
   * @return array
   */
  public function prepareAddOrEditRequestForSerialization(BaseObject $object, string ...$editableAttributes): array
  {
    $serialize = [];

    if (count($editableAttributes) === 0) {
      $editableAttributes = $object::getEditableAttributes();
    }

    foreach ($editableAttributes as $editableAttributeName) {
      if ($editableAttributeName == 'extraVeldenKlant') {
        continue;
      }
      $methodExists = false;
      $methodName = null;
      $methodNames = [
        "get" . \ucfirst($editableAttributeName),
        "is" . \ucfirst($editableAttributeName),
      ];

      foreach ($methodNames as $methodName) {
        if (\method_exists($object, $methodName)) {
          $methodExists = true;
          break;
        }
      }

      if (!$methodExists) {
        \trigger_error(sprintf("There is no method (get or is) on object %s for property %s", get_class($object), $editableAttributeName), \E_USER_NOTICE);
        continue;
      }

      $value = $object->{$methodName}();

      if ($value instanceof UuidInterface) {
        $value = $this->serializer->uuidInterfaceToString($value);
      } else if ($value instanceof \DateTimeInterface) {
        $value = $this->serializer->dateTimeToString($value);
      } elseif ($value instanceof Money) {
        $value = $this->serializer->moneyFormatToString($value);
      } else if ($editableAttributeName === "id" && $value === null) {
        // Whenever 'id' equals null skip it.
        $this->serializer->scalarValue($value);
        continue;
      } else if ($value instanceof \JsonSerializable || is_scalar($value) || $value === null) {
        // We accept simple values.
        $value = $this->serializer->scalarValue($value);
      } else if (is_array($value)) {
        // If our value is an array and contains anything that is an instance of 'BaseObject'
        // Try to serialize that again. Please note that this is done by reference.
        foreach ($value as &$subValue) {
          if ($subValue instanceof BaseObject) {
            $subValue = $this->prepareAddOrEditRequestForSerialization($subValue);
          }
        }

        // Else do nothing.
        $value = $this->serializer->arrayValue($value);
      } else if ($value instanceof SnelstartObject) {
        $editableSubAttributes = [];

        if ($value->getId() !== null) {
          // Because it is an existing sub-object we only have to pass the id.
          $editableSubAttributes = ["id"];
        }

        $value = $this->prepareAddOrEditRequestForSerialization($value, ...$editableSubAttributes);
      } else if ($value instanceof BaseObject) {
        $value = $this->prepareAddOrEditRequestForSerialization($value);
      } else {
        throw new \LogicException(sprintf(
          "You need to implement something to handle the serialization of '%s' (type: %s)",
          \get_class($value),
          \gettype($value)
        ));
      }

      $serialize[$editableAttributeName] = $value;
    }

    return $serialize;
  }
}