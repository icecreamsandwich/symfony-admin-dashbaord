<?php

namespace App\Entity;

use App\Repository\FirmwareVersionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FirmwareVersionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FirmwareVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $systemVersion = null;

    #[ORM\Column(length: 255)]
    private ?string $systemVersionAlt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $link = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $st = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $gd = null;

    #[ORM\Column]
    private bool $latest = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

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
        $this->name = trim($name);

        return $this;
    }

    public function getSystemVersion(): ?string
    {
        return $this->systemVersion;
    }

    public function setSystemVersion(string $systemVersion): static
    {
        $this->systemVersion = trim($systemVersion);

        return $this;
    }

    public function getSystemVersionAlt(): ?string
    {
        return $this->systemVersionAlt;
    }

    public function setSystemVersionAlt(?string $systemVersionAlt): static
    {
        $this->systemVersionAlt = $systemVersionAlt !== null ? ltrim(trim($systemVersionAlt), 'vV') : null;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = trim($link);

        return $this;
    }

    public function getSt(): ?string
    {
        return $this->st;
    }

    public function setSt(?string $st): static
    {
        $this->st = $st !== null && trim($st) !== '' ? trim($st) : null;

        return $this;
    }

    public function getGd(): ?string
    {
        return $this->gd;
    }

    public function setGd(?string $gd): static
    {
        $this->gd = $gd !== null && trim($gd) !== '' ? trim($gd) : null;

        return $this;
    }

    public function isLatest(): bool
    {
        return $this->latest;
    }

    public function setLatest(bool $latest): static
    {
        $this->latest = $latest;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->normalizeVersions();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->normalizeVersions();
    }

    private function normalizeVersions(): void
    {
        if ($this->systemVersion !== null) {
            $this->systemVersion = trim($this->systemVersion);
        }

        if ($this->systemVersionAlt === null || $this->systemVersionAlt === '') {
            $this->systemVersionAlt = $this->systemVersion;
        }

        if ($this->systemVersionAlt !== null) {
            $this->systemVersionAlt = ltrim(trim($this->systemVersionAlt), 'vV');
        }
    }
}
