<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100, maxMessage: 'Maximum 100 caractères')]
    #[Assert\NotBlank(message: 'Veuillez renseigner un Nom de lieu')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ -_]+$/',
        message: 'Le nom du lieu doit uniquement contenir des lettres, des espaces, et des tirets.'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Assert\Length(max: 5, maxMessage: 'Maximum 5 caractères')]
    private ?string $roadnumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roadname = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un Code postal')]
    #[Assert\Regex(
        pattern: '/^[0-9]{5}(?:-[0-9]{4})?$/',
        message: 'Le code postal doit être composé de 5 chiffres.'
    )]
    private ?string $zipcode = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un Nom de ville')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ -]+$/',
        message: 'Le nom de ville doit uniquement contenir des lettres, des espaces, et des tirets.'
    )]
    private ?string $townname = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\Range(
        min: -6,
        max: 10,
        notInRangeMessage: 'Longitude doit être entre {{ min }} et {{ max }}° Est',
    )]
    private ?string $longitude = null;

    // For reference, approx. bounding box of metropolitan france
    // 41.395564261621374, 9.622656317933236
    // 42.522508740823724, -5.271463860069315
    // 51.13108966622337, -4.957903435269262
    // 51.11468909409604, 8.290024512533009
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\Range(
        min: 40,
        max: 52,
        notInRangeMessage: 'Latitude doit être entre {{ min }} et {{ max }}° Nord',
    )]
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
