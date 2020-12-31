<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Seat implements JsonSerializable
{
    use EntitySerializableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private int $number;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank
     * @Assert\Type(type="float")
     */
    private float $locationX;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank
     * @Assert\Type(type="float")
     */
    private float $locationY;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     */
    private int $floorId;

    /**
     * @ORM\ManyToOne(targetEntity=Floor::class, inversedBy="seats")
     * @ORM\JoinColumn(nullable=false, fieldName="floorId")
     * @Assert\NotBlank()
     */
    private ?Floor $floor;

    /**
     * @ORM\OneToMany(targetEntity=SeatResource::class, mappedBy="seats")
     */
    private Collection $seatResources;

    /**
     * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="seat", orphanRemoval=true)
     */
    private Collection $bookings;

    public function __construct()
    {
        $this->seatResources = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function getLocationX(): float
    {
        return $this->locationX;
    }

    public function setLocationX(float $locationX): void
    {
        $this->locationX = $locationX;
    }

    public function getLocationY(): float
    {
        return $this->locationY;
    }

    public function setLocationY(float $locationY): void
    {
        $this->locationY = $locationY;
    }

    public function getSeatResources(): ArrayCollection | Collection
    {
        return $this->seatResources;
    }

    public function setSeatResources(ArrayCollection | Collection $seatResources): void
    {
        $this->seatResources = $seatResources;
    }

    public function getBookings(): ArrayCollection | Collection
    {
        return $this->bookings;
    }

    public function setBookings(ArrayCollection | Collection $bookings): void
    {
        $this->bookings = $bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setSeat($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getSeat() === $this) {
                $booking->setSeat(null);
            }
        }

        return $this;
    }

    public function getFloor(): ?Floor
    {
        return $this->floor;
    }

    public function setFloor(?Floor $floor): void
    {
        $this->floor = $floor;
    }
}
