<?php

namespace Krabo\SnelstartBundle\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\GuzzleException;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Payment;
use Isotope\Model\ProductCollectionSurcharge\Shipping;
use Isotope\Model\TaxClass;
use Isotope\Model\TaxRate;
use Krabo\SnelstartBundle\Factory;
use Krabo\SnelstartBundle\Snelstart\Grootboek;
use Krabo\SnelstartBundle\Snelstart\Kostenplaats;
use Krabo\SnelstartBundle\Snelstart\MemorialDagboek;
use Krabo\SnelstartBundle\Snelstart\Relatie;
use Money\Money;
use Krabo\SnelstartBundle\Connector\V2\MemorialBoekingConnector;
use Krabo\SnelstartBundle\Model\V2\Memorialboeking;
use Krabo\SnelstartBundle\Model\V2\MemorialBoekingsregel;
use SnelstartPHP\Connector\V2\BoekingConnector;
use SnelstartPHP\Model\Dagboek;
use SnelstartPHP\Model\Type\BtwRegelSoort;
use SnelstartPHP\Model\Type\BtwSoort;
use SnelstartPHP\Model\V2\Boekingsregel;
use SnelstartPHP\Model\V2\Btwregel;
use SnelstartPHP\Model\V2\Verkoopboeking;
use SnelstartPHP\Serializer\SnelstartRequestRequestSerializer;
use SnelstartPHP\Snelstart;

class IsotopeOrderSync
{

  /**
   * @var Factory
   */
  private $factory;

  /**
   * @var Relatie
   */
  private $relatie;

  /**
   * @var Grootboek
   */
  private $grootboek;

  /**
   * @var Kostenplaats
   */
  private $kostenplaats;

  /**
   * @var Connection
   */
  private $connection;

  /**
   * @var SnelstartBtw
   */
  private $btwHelper;

  public function __construct(Connection $connection, Factory $factory, Grootboek $grootboek, Relatie $relatie, Kostenplaats $kostenplaats, SnelstartBtw $btwHelper)
  {
    $this->connection = $connection;
    $this->factory = $factory;
    $this->relatie = $relatie;
    $this->grootboek = $grootboek;
    $this->kostenplaats = $kostenplaats;
    $this->btwHelper = $btwHelper;
  }

  /**
   * @param Order $order
   * @return Verkoopboeking|null
   */
  public function syncOrder(Order $order, $abort = FALSE)
  {
    try {
      $boekingsregels = [];
      $btwRegels = [];
      $btw = [];
      $factuurBedrag = 0;

      $orderIsAvailableForSync = true;
      if (isset($GLOBALS['SNELSTART_HOOKS']['order_available_for_sync']) && \is_array($GLOBALS['SNELSTART_HOOKS']['order_available_for_sync'])) {
        foreach ($GLOBALS['SNELSTART_HOOKS']['order_available_for_sync'] as $callback) {
          $objCallback = \System::importStatic($callback[0]);
          $orderIsAvailableForSync = $objCallback->{$callback[1]}($order, $orderIsAvailableForSync);
        }
      }
      if (!$orderIsAvailableForSync) {
        $order->snelstart_sync_date = time();
        $order->snelstart_error_message = '';
        $order->snelstart_id = 'n/a';
        $order->save();
        return null;
      }

      $btwPercentages = [];
      foreach ($order->getSurcharges() as $surcharge) {
        if ($surcharge instanceof ProductCollectionSurcharge\Tax && substr($surcharge->price, -1) === '%') {
          $btwPercentages[$surcharge->getTaxNumbers()] = substr($surcharge->price, 0, -1);
        }
      }

      foreach ($order->getItems() as $item) {
        $itemBoekingsregels = $this->itemToBoekingsRegel($item, $btw, $order, $btwPercentages);
        foreach ($itemBoekingsregels as $itemBoekingsregel) {
          $factuurBedrag = $factuurBedrag + $itemBoekingsregel->getBedrag()->getAmount();
          $boekingsregels[] = $itemBoekingsregel;
        }
      }

      // Voeg verzend kosten toe aan de factuur
      foreach ($order->getSurcharges() as $surcharge) {
        $surchargeBoekingsregels = $this->surchargeToBoekingsRegel($surcharge, $btw, $order, $btwPercentages);
        foreach ($surchargeBoekingsregels as $surchargeBoekingsregel) {
          $factuurBedrag = $factuurBedrag + $surchargeBoekingsregel->getBedrag()->getAmount();
          $boekingsregels[] = $surchargeBoekingsregel;
        }
      }

      if (isset($GLOBALS['SNELSTART_HOOKS']['boekingsregels']) && \is_array($GLOBALS['SNELSTART_HOOKS']['boekingsregels']))
      {
        foreach ($GLOBALS['SNELSTART_HOOKS']['boekingsregels'] as $callback)
        {
          $objCallback = \System::importStatic($callback[0]);
          $hookBoekingsRegels = $objCallback->{$callback[1]}($order, $boekingsregels, $btw, $btwPercentages);
          foreach ($hookBoekingsRegels as $hookBoekingsRegels) {
            $factuurBedrag = $factuurBedrag + $hookBoekingsRegels->getBedrag()->getAmount();
            $boekingsregels[] = $hookBoekingsRegels;
          }
        }
      }

      foreach ($btw as $btwRegelSoortKey => $btwBedrag) {
        $btwRegelSoort = BtwRegelSoort::from($btwRegelSoortKey);
        $factuurBedrag = $factuurBedrag + $btwBedrag;
        $btwRegels[] = new Btwregel($btwRegelSoort, Money::EUR((int)$btwBedrag));
      }

      $relatie = $this->relatie->getRelatieFromOrder($order);

      $omschrijving = $order->document_number;
      $verkoopBoeking = new Verkoopboeking();
      $verkoopBoeking->setKlant($relatie)
        ->setFactuurdatum(new \DateTime())
        // Gebruiker order id omdat die ook aan Mollie wordt doorgegeven.
        // Op deze manier kunnen in snelstart dan de betalingen gematched worden.
        ->setFactuurnummer($order->id)
        ->setOmschrijving($omschrijving)
        ->setFactuurbedrag(\Money\Money::EUR($factuurBedrag))
        ->setBoekingsregels(...$boekingsregels)
        ->setBtw(...$btwRegels);

      if (isset($GLOBALS['SNELSTART_HOOKS']['book_order']) && \is_array($GLOBALS['SNELSTART_HOOKS']['book_order'])) {
        foreach ($GLOBALS['SNELSTART_HOOKS']['book_order'] as $callback) {
          $objCallback = \System::importStatic($callback[0]);
          $objCallback->{$callback[1]}($order, $verkoopBoeking);
        }
      }

      $debug = '';
      foreach($verkoopBoeking->getBoekingsregels() as $boekingsregel) {
        /** @var \SnelstartPHP\Model\V2\Boekingsregel $boekingsregel */
        $debug .= $boekingsregel->getGrootboek()->getNummer() . ': ' . $boekingsregel->getOmschrijving() . ': EURO ' . $boekingsregel->getBedrag()->getAmount() . "\r\n";
      }
      $debug .= "BTW REGELS\r\n";
      foreach($verkoopBoeking->getBtw() as $btwRegel) {
        $debug .= $btwRegel->getBtwSoort()->getValue() . ': EURO ' . $btwRegel->getBtwBedrag()->getAmount() . "\r\n";
      }
      $debug .= "FACTUUR TOTAAL: " . $verkoopBoeking->getFactuurbedrag()->getAmount();
      

      $boekingConnector = new BoekingConnector($this->factory->getConnection());
      $verkoopBoeking = $boekingConnector->addVerkoopboeking($verkoopBoeking);

      $order->snelstart_sync_date = time();
      $order->snelstart_error_message = '';
      $order->snelstart_debug = $debug;
      if ($verkoopBoeking && $verkoopBoeking->getId()) {
        $order->snelstart_id = $verkoopBoeking->getId()->toString();
      }
      $order->save();

      if (isset($GLOBALS['SNELSTART_HOOKS']['post_book_order']) && \is_array($GLOBALS['SNELSTART_HOOKS']['post_book_order'])) {
        foreach ($GLOBALS['SNELSTART_HOOKS']['post_book_order'] as $callback) {
          $objCallback = \System::importStatic($callback[0]);
          $objCallback->{$callback[1]}($order, $verkoopBoeking);
        }
      }


      return $verkoopBoeking;
    } catch (\Exception $ex) {
      $order->snelstart_sync_date = time();
      $order->snelstart_id = '';
      $order->snelstart_error_message = var_export($verkoopBoeking, true);
      $order->snelstart_error_message .= "\r\n\r\n" . $ex->getMessage() . ' [in ' . $ex->getFile() . ': ' . $ex->getLine() . ']';
      $order->snelstart_error_message .= "\r\n\r\n" . $ex->getTraceAsString();
      $order->save();
      if ($abort) {
        throw $ex;
      }
    }
    return null;
  }

  /**
   * @param ProductCollectionItem $item
   * @param array $arrTaxes
   * @param Order $order
   * @param array $btwPercentages
   * @param int $tax_class_id
   * @return array Boekingsregel
   */
  protected function itemToBoekingsRegel(ProductCollectionItem $item, array &$arrTaxes, Order $order, $btwPercentages)
  {
    $omschrijving = $item->quantity . 'x ' . \StringUtil::decodeEntities($item->getName());
    if (isset($btwPercentages[$item->tax_id])) {
      // Calcalate the vat percentage and the vat amount.
      // We need to use a little hack here as the percentage is stored at a tax surcharge level
      // and the amount excluding vat is not stored in tax_free_total_price. The latter is a bug in isotope.
      $btwPercentage = $btwPercentages[$item->tax_id];
      $btwBedrag = $btwPercentage * $item->getTotalPrice() / (100 + $btwPercentage);
      $bedragExBtw = 100 * $item->getTotalPrice() / (100 + $btwPercentage);
    } else {
      $btwPercentage = 0;
      $btwBedrag = $item->getTotalPrice() - $item->getTaxFreeTotalPrice();
      $bedragExBtw = $item->getTaxFreeTotalPrice();
    }
    $btwBedrag = (int)round($btwBedrag * 100);
    $bedragExBtw = (int)round($bedragExBtw * 100);
    $boekingsRegels = $this->createBoekingsRegel($omschrijving, $btwBedrag, $bedragExBtw, $order, $arrTaxes, $btwPercentage);
    if (isset($GLOBALS['SNELSTART_HOOKS']['itemToBoekingsRegels']) && \is_array($GLOBALS['SNELSTART_HOOKS']['itemToBoekingsRegels'])) {
      foreach ($GLOBALS['SNELSTART_HOOKS']['itemToBoekingsRegels'] as $callback) {
        $objCallback = \System::importStatic($callback[0]);
        $objCallback->{$callback[1]}($boekingsRegels, $item, $omschrijving, $btwBedrag, $bedragExBtw, $order, $arrTaxes, $btwPercentages);
      }
    }
    return $boekingsRegels;
  }

  /**
   * @param ProductCollectionSurcharge $surcharge
   * @param array $arrTaxes
   * @param Order $order
   * @param array $btwPercentages
   * @return array Boekingsregel
   */
  protected function surchargeToBoekingsRegel(ProductCollectionSurcharge $surcharge, array &$arrTaxes, Order $order, $btwPercentages)
  {
    $omschrijving = \StringUtil::decodeEntities($surcharge->label);
    if ($surcharge instanceof Shipping || $surcharge instanceof Payment) {
      if ($surcharge->total_price === $surcharge->tax_free_total_price && isset($btwPercentages[$surcharge->getTaxNumbers()])) {
        // Calcalate the vat percentage and the vat amount.
        // We need to use a little hack here as the percentage is stored at a tax surcharge level
        // and the amount excluding vat is not stored in tax_free_total_price. The latter is a bug in isotope.
        $btwPercentage = $btwPercentages[$surcharge->getTaxNumbers()];
        $btwBedrag = $btwPercentage * $surcharge->total_price / (100 + $btwPercentage);
        $bedragExBtw = 100 * $surcharge->total_price / (100 + $btwPercentage);
      } else {
        $btwPercentage = 0;
        $btwBedrag = $surcharge->total_price - $surcharge->tax_free_total_price;
        $bedragExBtw = $surcharge->tax_free_total_price;
        $btwPercentage = 0;
        if ($bedragExBtw > 0) {
          $btwPercentage = (int)round(($btwBedrag * 100) / $bedragExBtw);
        }
      }
      $btwBedrag = (int)round($btwBedrag * 100);
      $bedragExBtw = (int)round($bedragExBtw * 100);
      return $this->createBoekingsRegel($omschrijving, $btwBedrag, $bedragExBtw, $order, $arrTaxes, $btwPercentage);
    }
    return [];
  }

  /**
   *
   * @param $omschrijving
   * @param $btwBedrag
   * @param $bedragExBtw
   * @param Order $order
   * @param array $arrTaxes
   * @param int $btwPercentage
   * @return array Boekingsregel
   */
  public function createBoekingsRegel($omschrijving, $btwBedrag, $bedragExBtw, Order $order, array &$arrTaxes, $btwPercentage)
  {
    $btwSoort = $this->btwHelper->getBtwSoort($btwPercentage, $order);
    $btwRegelSoort = BtwRegelSoort::GEEN();
    if ($btwSoort == BtwSoort::HOOG()) {
      $btwRegelSoort = BtwRegelSoort::VERKOPENHOOG();
    } elseif ($btwSoort == BtwSoort::LAAG()) {
      $btwRegelSoort = BtwRegelSoort::VERKOPENLAAG();
    }
    $btwGrootBoek = false;
    $grootBoek = $this->grootboek->findGrootboekRekening($this->btwHelper->getGrootBoekRekening($btwPercentage, $order));
    if ($this->btwHelper->isEU($order) && $this->grootboek->getEUBtwGrootboekRekening()) {
      $btwGrootBoek = $this->grootboek->getEUBtwGrootboekRekening();
    }
    if ($grootBoek) {
      $kostenplaats = $this->kostenplaats->getKostenplaats();
      $boekingsRegel = new Boekingsregel();
      $boekingsRegel->setOmschrijving($omschrijving);
      $boekingsRegel->setGrootboek($grootBoek);
      $boekingsRegel->setBtwSoort($btwSoort);
      if ($kostenplaats) {
        $boekingsRegel->setKostenplaats($kostenplaats);
      }

      if ($boekingsRegel->getBtwSoort() != BtwSoort::GEEN()) {
        $boekingsRegel->setBedrag(\Money\Money::EUR($bedragExBtw));
        if (!isset($arrTaxes[$btwRegelSoort->getValue()])) {
          $arrTaxes[$btwRegelSoort->getValue()] = 0;
        }
        $arrTaxes[$btwRegelSoort->getValue()] = $arrTaxes[$btwRegelSoort->getValue()] + $btwBedrag;
        return [$boekingsRegel];
      } elseif ($btwGrootBoek) {
        $boekingsRegel->setBedrag(\Money\Money::EUR($bedragExBtw));
        $btwBoekingsRegel = new Boekingsregel();
        $btwBoekingsRegel->setOmschrijving($omschrijving);
        $btwBoekingsRegel->setGrootboek($btwGrootBoek);
        $btwBoekingsRegel->setBtwSoort($btwSoort);
        if ($kostenplaats) {
          $btwBoekingsRegel->setKostenplaats($kostenplaats);
        }
        $btwBoekingsRegel->setBedrag(\Money\Money::EUR($btwBedrag));
        return [$boekingsRegel, $btwBoekingsRegel];
      } else {
        $boekingsRegel->setBedrag(\Money\Money::EUR($bedragExBtw + $btwBedrag));
        return [$boekingsRegel];
      }
    }
    return [];
  }

}
