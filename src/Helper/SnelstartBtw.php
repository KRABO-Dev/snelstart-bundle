<?php

namespace Krabo\SnelstartBundle\Helper;

use Isotope\Model\ProductCollection\Order;
use SnelstartPHP\Model\Type\BtwSoort;

class SnelstartBtw {

    protected $omzet_hoog_nl;

    protected $omzet_laag_nl;

    protected $omzet_eu_standaard;

    protected $omzet_eu_verlaagd_hoog;

    protected $omzet_eu_verlaagd_laag;

    protected $omzet_eu_super_verlaagd;

    protected $omzet_eu_geen;

    protected $omzet_wereld;

    public function __construct($omzet_hoog_nl, $omzet_laag_nl, $omzet_eu_standaard, $omzet_eu_verlaagd_hoog, $omzet_eu_verlaagd_laag, $omzet_eu_super_verlaagd, $omzet_eu_geen, $omzet_wereld) {
        $this->omzet_hoog_nl = $omzet_hoog_nl;
        $this->omzet_laag_nl = $omzet_laag_nl;

        $this->omzet_eu_standaard = $omzet_eu_standaard;
        $this->omzet_eu_verlaagd_hoog = $omzet_eu_verlaagd_hoog;
        $this->omzet_eu_verlaagd_laag = $omzet_eu_verlaagd_laag;
        $this->omzet_eu_super_verlaagd = $omzet_eu_super_verlaagd;
        $this->omzet_eu_geen = $omzet_eu_geen;
        $this->omzet_wereld = $omzet_wereld;
    }

    public function getBtwLand(Order $order) {
      $objBillingAddress = $order->getShippingAddress();
      $land = 'nl';
      if ($objBillingAddress) {
        $land = $objBillingAddress->country;
      }
      return $land;
    }

    public function getGrootBoekRekening($btwPercentage, Order $order) {
        $land = $this->getBtwLand($order);
        switch (strtolower($land)) {
            case 'nl': // Nederland
                if ($btwPercentage == 9) {
                    return $this->omzet_laag_nl;
                }
                return $this->omzet_hoog_nl;
                break;
            case 'at': // Oostenrijk
                if ($btwPercentage == 10) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'be': // Belgie
                if ($btwPercentage == 6) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'bg': // Bulgarije
                return $this->omzet_eu_standaard;
                break;
            case 'cy': // Cyprus
                if ($btwPercentage == 5) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'cz': // Tsechie
                if ($btwPercentage == 12) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'de': // Duitsland
                if ($btwPercentage == 7) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'dk': // Denemarken
                return $this->omzet_eu_standaard;
                break;
            case 'ee': // Estland (Estonia)
                if ($btwPercentage == 9) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'es': // Spanje
                if ($btwPercentage == 4) {
                    return $this->omzet_eu_super_verlaagd;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'fi': // Finland
                if ($btwPercentage == 14 || $btwPercentage == 13.5) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'fr': // Frankrijk
                if ($btwPercentage != 20) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'gr': // Griekenland
                if ($btwPercentage == 6) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'hr': // Kroatie
                if ($btwPercentage == 5) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'hu': // Hongarije
                if ($btwPercentage == 5) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'ie': // Ierland
                if ($btwPercentage != 23) {
                    return $this->omzet_eu_geen;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'it': // Italie
                if ($btwPercentage == 4) {
                    return $this->omzet_eu_super_verlaagd;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'lt': // Litouwen
                return $this->omzet_eu_standaard;
                break;
            case 'lu': // Luxemburg
                if ($btwPercentage == 3) {
                    return $this->omzet_eu_super_verlaagd;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'lv': // Letland (Latvia)
                return $this->omzet_eu_standaard;
                break;
            case 'mt': // Malta
                if ($btwPercentage == 5) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'pl': // Polen
                if ($btwPercentage == 5) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'pt': // Portugal
                if ($btwPercentage == 6) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'ro': // Roemenie
                if ($btwPercentage == 11) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'se': // Finland
                if ($btwPercentage == 6) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'si': // Slovenie
                if ($btwPercentage != 22) {
                    return $this->omzet_eu_verlaagd_hoog;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'sk': // Slowakije
                if ($btwPercentage == 19 || $btwPercentage == 5) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
                break;
            case 'xi': // Noord-Ierland
                if ($btwPercentage == 5) {
                    return $this->omzet_eu_verlaagd_laag;
                }
                return $this->omzet_eu_standaard;
        }
        return $this->omzet_wereld;
    }

    public function getBtwSoort($btwPercentage, $order) {
        $land = $this->getBtwLand($order);
        switch (strtolower($land)) {
            case 'nl':
                if ($btwPercentage == 9) {
                    return BtwSoort::LAAG();
                }
                return BtwSoort::HOOG();
                break;
        }
        return BtwSoort::GEEN();
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function isNL(Order $order) {
      $land = $this->getBtwLand($order);
        switch (strtolower($land)) {
            case 'nl':
                return TRUE;
                break;
        }
        return FALSE;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function isEU(Order $order) {
        $land = $this->getBtwLand($order);
        switch (strtolower($land)) {
            case 'at':
            case 'be':
            case 'bg':
            case 'cy':
            case 'cz':
            case 'dk':
            case 'de':
            case 'ee':
            case 'gr':
            case 'es':
            case 'ie':
            case 'fi':
            case 'fr':
            case 'hr':
            case 'hu':
            case 'it':
            case 'lt':
            case 'lu':
            case 'lv':
            case 'mt':
            case 'pl':
            case 'pt':
            case 'ro':
            case 'se':
            case 'si':
            case 'sk':
            case 'xi':    
                return TRUE;
                break;
        }
        return FALSE;
    }

}
