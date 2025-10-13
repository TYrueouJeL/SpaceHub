<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\ManyToOne(inversedBy: 'places')]
    private ?PlaceType $type = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'place')]
    private Collection $reservations;

    /**
     * @var Collection<int, PlaceEquipement>
     */
    #[ORM\OneToMany(targetEntity: PlaceEquipement::class, mappedBy: 'place')]
    private Collection $placeEquipements;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'place')]
    private Collection $reviews;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->placeEquipements = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getType(): ?PlaceType
    {
        return $this->type;
    }

    public function setType(?PlaceType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setPlace($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getPlace() === $this) {
                $reservation->setPlace(null);
            }
        }

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
            $placeEquipement->setPlace($this);
        }

        return $this;
    }

    public function removePlaceEquipement(PlaceEquipement $placeEquipement): static
    {
        if ($this->placeEquipements->removeElement($placeEquipement)) {
            // set the owning side to null (unless already changed)
            if ($placeEquipement->getPlace() === $this) {
                $placeEquipement->setPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setPlace($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getPlace() === $this) {
                $review->setPlace(null);
            }
        }

        return $this;
    }
}
