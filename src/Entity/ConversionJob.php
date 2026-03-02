<?php

namespace App\Entity;

use App\Repository\ConversionJobRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ConversionJobRepository::class)]
class ConversionJob
{
    public const STATUS_PENDING = "PENDING";
    public const STATUS_PROCESSING = "PROCESSING";
    public const STATUS_DONE = "DONE";
    public const STATUS_FAILED = "FAILED";

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255)]
    private ?string $inputPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $outputPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $outputFormat = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(string $inputPath, string $outputFormat)
    {
        $this->id = Uuid::v7();
        $this->status = self::STATUS_PENDING;
        $this->inputPath = $inputPath;
        $this->outputFormat = $outputFormat;
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getIdAsString(): string
    {
        return (string) $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;

        $this->touch();
    }

    public function getInputPath(): ?string
    {
        return $this->inputPath;
    }

    public function setInputPath(string $inputPath): static
    {
        $this->inputPath = $inputPath;

        return $this;
    }

    public function getOutputPath(): ?string
    {
        return $this->outputPath;
    }

    public function setOutputPath(?string $outputPath): void
    {
        $this->outputPath = $outputPath;

        $this->touch();
    }

    public function getOutputFormat(): ?string
    {
        return $this->outputFormat;
    }

    public function setOutputFormat(?string $outputFormat): static
    {
        $this->outputFormat = $outputFormat;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
