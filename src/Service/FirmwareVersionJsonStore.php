<?php

namespace App\Service;

use App\Entity\FirmwareVersion;
use App\Repository\FirmwareVersionRepository;
use Doctrine\ORM\EntityManagerInterface;

class FirmwareVersionJsonStore
{
    public function __construct(
        private readonly string $projectDir,
        private readonly FirmwareVersionRepository $firmwareVersionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function initializeFromJsonIfEmpty(): void
    {
        if ($this->firmwareVersionRepository->count([]) > 0) {
            return;
        }

        $path = $this->getJsonPath();
        if (!is_file($path)) {
            return;
        }

        $rows = json_decode((string) file_get_contents($path), true);
        if (!is_array($rows)) {
            return;
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $version = new FirmwareVersion();
            $version
                ->setName((string) ($row['name'] ?? ''))
                ->setSystemVersion((string) ($row['system_version'] ?? ''))
                ->setSystemVersionAlt((string) ($row['system_version_alt'] ?? ($row['system_version'] ?? '')))
                ->setLink((string) ($row['link'] ?? ''))
                ->setSt($row['st'] ?? null)
                ->setGd($row['gd'] ?? null)
                ->setLatest((bool) ($row['latest'] ?? false));

            $this->entityManager->persist($version);
        }

        $this->entityManager->flush();
    }

    public function exportAllToJson(): void
    {
        $rows = array_map(
            static fn (FirmwareVersion $version): array => [
                'name' => $version->getName(),
                'system_version' => $version->getSystemVersion(),
                'system_version_alt' => $version->getSystemVersionAlt(),
                'link' => $version->getLink(),
                'st' => $version->getSt() ?? '',
                'gd' => $version->getGd() ?? '',
                'latest' => $version->isLatest(),
            ],
            $this->firmwareVersionRepository->findAllOrdered(),
        );

        $path = $this->getJsonPath();
        $directory = \dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents(
            $path,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
        );
    }

    public function getJsonPath(): string
    {
        return $this->projectDir.'/data/firmware/softwareversions.json';
    }
}
