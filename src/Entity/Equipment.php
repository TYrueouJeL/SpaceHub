<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, PlaceEquipement>
     */
    #[ORM\OneToMany(targetEntity: PlaceEquipement::class, mappedBy: 'equipment')]
    private Collection $placeEquipements;

    public function __construct()
    {
        $this->placeEquipements = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, PlaceEquipement>
     */
    public function getPlaceEquipements(): Collection
    {
        return $this->placeEquipements;
    }

    public function addPlaceEquipement(PlaceEquipement $placeEquipement): static
    {
        if (!$this->placeEquipements->contains($placeEquipement)) {
            $this->placeEquipements->add($placeEquipement);
            $placeEquipement->setEquipment($this);
        }

        return $this;
    }

    public function removePlaceEquipement(PlaceEquipement $placeEquipement): static
    {
        if ($this->placeEquipements->removeElement($placeEquipement)) {
            // set the owning side to null (unless already changed)
            if ($placeEquipement->getEquipment() === $this) {
                $placeEquipement->setEquipment(null);
            }
        }

        return $this;
    }
}
