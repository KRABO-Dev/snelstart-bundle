<?php

namespace Krabo\SnelstartBundle\Snelstart;

use Contao\StringUtil;
use Isotope\Model\ProductCollection\Order;
use Krabo\SnelstartBundle\Factory;
use SnelstartPHP\Connector\V2\LandConnector;
use SnelstartPHP\Exception\PreValidationException;
use SnelstartPHP\Mapper\V2\RelatieMapper;
use SnelstartPHP\Model\Type\Relatiesoort;

class Relatie {

  /**
   * @var Factory
   */
  private $factory;

  public function __construct(Factory $factory) {
    $this->factory = $factory;
  }

  /**
   *
   * @param Order $order
   *
   * @return \SnelstartPHP\Model\V2\Relatie
   */
  public function getRelatieFromOrder(Order $order) {
    $objBillingAddress = $this->getBillingAddressFromOrder($order);

    $relatie = new \SnelstartPHP\Model\V2\Relatie();
    //Opvangen van pick-up point door factuuradres
    if (isset($objBillingAddress->sendcloud_servicepoint_id) && $objBillingAddress->sendcloud_servicepoint_id > 0) {
      $objBilling = $order->getBillingAddress();
      $relatie->setNaam(StringUtil::substr(StringUtil::decodeEntities($objBilling->salutation . ' ' . $objBilling->firstname . ' ' . $objBilling->lastname), 50, FALSE));
      $relatie->setEmail($objBilling->email);
    }
    else {
      $relatie->setNaam(StringUtil::substr(StringUtil::decodeEntities($objBillingAddress->salutation . ' ' . $objBillingAddress->firstname . ' ' . $objBillingAddress->lastname), 50, FALSE));
      $relatie->setEmail($objBillingAddress->email);
    }

    $adres = new \SnelstartPHP\Model\Adres();

    $land = $this->findLand(strtoupper($objBillingAddress->country));
    $adres->setStraat(StringUtil::substr(StringUtil::decodeEntities($objBillingAddress->street_1 . " " . $objBillingAddress->housenumber), 50, FALSE));
    $adres->setPostcode(StringUtil::decodeEntities($objBillingAddress->postal));
    $adres->setPlaats(StringUtil::decodeEntities($objBillingAddress->city));
    $adres->setLand($land);
    $relatie->setVestigingsAdres($adres);
    $relatie->setCorrespondentieAdres($adres);
    $relatie->setRelatiesoort(Relatiesoort::KLANT());
    $relatie = $this->add($relatie);
    $relatie->getVestigingsAdres()->setLand($land);
    $relatie->getCorrespondentieAdres()->setLand($land);

    if (isset($GLOBALS['SNELSTART_HOOKS']['create_relation']) && \is_array($GLOBALS['SNELSTART_HOOKS']['create_relation']))
    {
      foreach ($GLOBALS['SNELSTART_HOOKS']['create_relation'] as $callback)
      {
        $objCallback = \System::importStatic($callback[0]);
        $objCallback->{$callback[1]}($order, $relatie);
      }
    }

    return $relatie;
  }

  /**
   * @param Order $order
   *
   * @return Isotope\Model\Address
   */
  protected function getBillingAddressFromOrder(Order $order) {
    // Use Shipping Address as the VAT is calculated based on the shipping address.
    // That is EU law which says that the VAT is applied on the delivery address of the goods.
    $shippingAddress = $order->getShippingAddress();
    if (empty($shippingAddress)) {
      $shippingAddress = $order->getBillingAddress();
    }
    return $shippingAddress;
  }

  /**
   * @param $landIsoCode
   *
   * @return \SnelstartPHP\Model\Land|null
   */
  public function findLand($landCode) {
    static $landConnector = NULL;
    static $cachedLanden = [];
    if ($landCode == 'GB') {
      // Indien land Verenigd Koninkrijk is, dan de code XU gebruiken,
      // dat is verenigd koninkrijk zonder noord ierland.
      // Zie ook: https://kennisplein.snelstart.nl/klanten/s/article/de-brexit#belangrijkeaanpassingen
      $landCode = 'XU';
    }

    if (isset($cachedLanden[$landCode])) {
      return $cachedLanden[$landCode];
    }

    if (!$landConnector) {
      $landConnector = new LandConnector($this->factory->getConnection());
    }

    $landen = $landConnector->findAll();
    foreach ($landen as $land) {
      $cachedLanden[$land->getLandcode()] = $land;
    }

    if (isset($cachedLanden[$landCode])) {
      return $cachedLanden[$landCode];
    }

    return NULL;
  }

  public function add(\SnelstartPHP\Model\V2\Relatie $relatie): \SnelstartPHP\Model\V2\Relatie {
    if ($relatie->getId() !== NULL) {
      throw PreValidationException::unexpectedIdException();
    }

    $mapper = new RelatieMapper();
    $request = new \Krabo\SnelstartBundle\Request\V2\RelatieRequest();

    return $mapper->add($this->factory->getConnection()
      ->doRequest($request->add($relatie)));
  }

  public function update(\SnelstartPHP\Model\V2\Relatie $relatie): \SnelstartPHP\Model\V2\Relatie {
    if ($relatie->getId() === NULL) {
      throw PreValidationException::unexpectedIdException();
    }

    $mapper = new RelatieMapper();
    $request = new \Krabo\SnelstartBundle\Request\V2\RelatieRequest();

    return $mapper->update($this->factory->getConnection()
      ->doRequest($request->update($relatie)));
  }

}
