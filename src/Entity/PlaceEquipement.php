<?php

namespace App\Entity;

use App\Repository\PlaceEquipementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceEquipementRepository::class)]
class PlaceEquipement
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'placeEquipements')]
    private ?Place $place = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'placeEquipements')]
    private ?Equipment $equipment = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function setCreatedAt()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
