<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Floor implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(nullable=true, type="integer")
     */
    private ?int $number;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     */
    private string $floorPath;

    /**
     * @ORM\ManyToOne(targetEntity=Building::class, inversedBy="floors")
     * @ORM\JoinColumn(nullable=false, fieldName="buildingId")
     * @Assert\NotBlank
     */
    private ?Building $building;

    /**
     * @ORM\OneToMany(targetEntity=Seat::class, mappedBy="floor", orphanRemoval=true)
     */
    private Collection $seats;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): void
    {
        $this->number = $number;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(?Building $building): void
    {
        $this->building = $building;
    }

    public function getFloorPath(): string
    {
        return $this->floorPath;
    }

    public function setFloorPath(string $floorPath): void
    {
        $this->floorPath = $floorPath;
    }

    public function getSeats(): Collection
    {
        return $this->seats;
    }

    public function setSeats(Collection $seats): void
    {
        $this->$seats = $seats;
    }

    public function addSeat(Seat $seat): self
    {
        if (!$this->seats->contains($seat)) {
            $this->seats[] = $seat;
            $seat->setFloor($this);
        }

        return $this;
    }

    public function removeSeat(Seat $seat): self
    {
        if ($this->seats->removeElement($seat)) {
            // set the owning side to null (unless already changed)
            if ($seat->getFloor() === $this) {
                $seat->setFloor(null);
            }
        }

        return $this;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
