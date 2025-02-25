<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $roadnumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roadname = null;

    #[ORM\Column(length: 5)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 255)]
    private ?string $townname = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?string $latitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $extraInfo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRoadnumber(): ?string
    {
        return $this->roadnumber;
    }

    public function setRoadnumber(?string $roadnumber): static
    {
        $this->roadnumber = $roadnumber;

        return $this;
    }

    public function getRoadname(): ?string
    {
        return $this->roadname;
    }

    public function setRoadname(?string $roadname): static
    {
        $this->roadname = $roadname;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getTownname(): ?string
    {
        return $this->townname;
    }

    public function setTownname(string $townname): static
    {
        $this->townname = $townname;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getExtraInfo(): ?string
    {
        return $this->extraInfo;
    }

    public function setExtraInfo(?string $extraInfo): static
    {
        $this->extraInfo = $extraInfo;

        return $this;
    }
}
