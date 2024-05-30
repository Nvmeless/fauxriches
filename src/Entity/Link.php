<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\LinkRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["default"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["default"])]
    private ?string $url = null;

    #[Groups(["default"])]
    private ?string $qrCode = null;
    
    #[ORM\Column(length: 25)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Song>
     */
    #[ORM\ManyToMany(targetEntity: Song::class, inversedBy: 'links')]
    #[Groups(["default"])]
    private Collection $song;

    public function __construct()
    {
        $this->song = new ArrayCollection();
        // $this->setQrCode();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQrCode(): ?string {

        return $this->qrCode;
    }

    public function setQrCode( string $qrCode):static {

        $this->qrCode = $qrCode;
        return $this;
    }
    public function getUrl(): ?string
    {


        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSong(): Collection
    {
        return $this->song;
    }

    public function addSong(Song $song): static
    {
        if (!$this->song->contains($song)) {
            $this->song->add($song);
        }

        return $this;
    }

    public function removeSong(Song $song): static
    {
        $this->song->removeElement($song);

        return $this;
    }
}
