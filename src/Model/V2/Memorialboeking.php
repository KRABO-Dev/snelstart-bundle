<?php

namespace Krabo\SnelstartBundle\Model\V2;

use Money\Money;
use SnelstartPHP\Exception\BookingNotInBalanceException;
use SnelstartPHP\Model\SnelstartObject;
use SnelstartPHP\Model\V2\Boekingsregel;

final class Memorialboeking extends SnelstartObject
{

    protected $memorialboekingsregels = [];

    protected $verkoopboekingBoekingsRegels;

    protected $inkoopboekingBoekingsRegels;

    /**
     * Het boekstuknummer van de boeking.
     *
     * @var string|null
     */
    protected $boekstuk;

    /**
     * Geeft aan of deze boeking is aangepast door de accountant.
     *
     * @var bool
     */
    protected $gewijzigdDoorAccountant = false;

    /**
     * Deze boeking verdient speciale aandacht, in SnelStart wordt dit visueel benadrukt.
     *
     * @var bool
     */
    protected $markering = false;

    /**
     * De omschrijving van de boeking.
     *
     * @var string|null
     */
    protected $omschrijving;

    /**
     * De datum van de boeking, dit is ook de datum waarop de boeking wordt geboekt.
     *
     * @var \DateTimeInterface|null
     */
    protected $datum;

    /**
     * @var \DateTimeInterface|null
     */
    protected $modifiedOn;

    /**
     * @var
     */
    protected $dagboek;

    /**
     * @var string[]
     */
    public static $editableAttributes = [
        "id",
        "boekstuk",
        "gewijzigdDoorAccountant",
        "markering",
        "omschrijving",
        "memoriaalBoekingsRegels",
        "inkoopboekingBoekingsRegels",
        "verkoopboekingBoekingsRegels",
        "datum",
        "dagboek"
    ];

    public static function getEditableAttributes(): array
    {
        return \array_unique(
            \array_merge(parent::$editableAttributes, parent::getEditableAttributes(), static::$editableAttributes, self::$editableAttributes)
        );
    }

    public function getBoekstuk(): ?string
    {
        return $this->boekstuk;
    }

    public function setBoekstuk(?string $boekstuk): self
    {
        $this->boekstuk = $boekstuk;

        return $this;
    }

    public function isGewijzigdDoorAccountant(): bool
    {
        return $this->gewijzigdDoorAccountant;
    }

    public function setGewijzigdDoorAccountant(bool $gewijzigdDoorAccountant): self
    {
        $this->gewijzigdDoorAccountant = $gewijzigdDoorAccountant;

        return $this;
    }

    public function isMarkering(): bool
    {
        return $this->markering;
    }

    public function setMarkering(bool $markering): self
    {
        $this->markering = $markering;

        return $this;
    }

    public function getOmschrijving(): ?string
    {
        return $this->omschrijving;
    }

    public function setOmschrijving(?string $omschrijving): self
    {
        $this->omschrijving = $omschrijving;

        return $this;
    }

    public function getMemoriaalBoekingsRegels(): array
    {
        return $this->memorialboekingsregels;
    }

    public function setMemoriaalBoekingsRegels($memorialboekingsregels): self
    {
        $this->memorialboekingsregels = $memorialboekingsregels;

        return $this;
    }

    public function getInkoopboekingBoekingsRegels() {
        return $this->inkoopboekingBoekingsRegels;
    }

    public function setInkoopboekingBoekingsRegels($boekingsRegels) {
        $this->inkoopboekingBoekingsRegels = $boekingsRegels;
        return $this;
    }

    public function getVerkoopboekingBoekingsRegels() {
        return $this->verkoopboekingBoekingsRegels;
    }

    public function setVerkoopboekingBoekingsRegels($boekingsRegels) {
        $this->verkoopboekingBoekingsRegels = $boekingsRegels;
        return $this;
    }

    public function getBoekingsregels(): array
    {
        return $this->memorialboekingsregels;
    }

    public function setBoekingsregels($memorialboekingsregels): self
    {
        $this->memorialboekingsregels = $memorialboekingsregels;

        return $this;
    }

    public function getDatum(): ?\DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(?\DateTimeInterface $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function setModifiedOn(?\DateTimeInterface $modifiedOn): self {
        $this->modifiedOn = $modifiedOn;
        return $this;
    }

    public function getDagboek()
    {
        return $this->dagboek;
    }

    public function setDagboek($dagboek): self
    {
        $this->dagboek = $dagboek;

        return $this;
    }

    public function assertInBalance(): void
    {
        return;
    }

}