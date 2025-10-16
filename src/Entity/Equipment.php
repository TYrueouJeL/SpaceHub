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

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, PlaceEquipement>
     */
    #[ORM\OneToMany(targetEntity: PlaceEquipement::class, mappedBy: 'equipment')]
    private Collection $placeEquipements;

    #[ORM\Column(length: 255)]
    private ?string $icon = null;

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

    public function setCreatedAt()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }
}
