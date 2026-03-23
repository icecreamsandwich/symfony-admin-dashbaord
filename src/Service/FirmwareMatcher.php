<?php

namespace App\Service;

use App\Entity\FirmwareVersion;
use App\Repository\FirmwareVersionRepository;

class FirmwareMatcher
{
    public function __construct(
        private readonly FirmwareVersionRepository $firmwareVersionRepository,
    ) {
    }

    /**
     * @return array{versionExist: bool, msg: string, link: string, st: string, gd: string}
     */
    public function match(?string $systemVersion, ?string $hardwareVersion): array
    {
        $systemVersion = trim((string) $systemVersion);
        $hardwareVersion = trim((string) $hardwareVersion);

        if ($systemVersion === '') {
            return $this->error('Version is required');
        }

        if ($hardwareVersion === '') {
            return $this->error('HW Version is required');
        }

        $hardware = $this->detectHardware($hardwareVersion);
        if ($hardware === null) {
            return $this->error('There was a problem identifying your software. Contact us for help.');
        }

        $version = null;
        foreach ($this->firmwareVersionRepository->findByNormalizedSystemVersion($systemVersion) as $candidate) {
            if ($this->isCompatible($candidate, $hardware)) {
                $version = $candidate;
                break;
            }
        }

        if ($version === null) {
            return $this->error('There was a problem identifying your software. Contact us for help.');
        }

        if ($version->isLatest()) {
            return [
                'versionExist' => true,
                'msg' => 'Your system is up to date!',
                'link' => '',
                'st' => '',
                'gd' => '',
            ];
        }

        $latest = $this->firmwareVersionRepository->findLatestForName((string) $version->getName());
        $latestVersion = $latest?->getSystemVersion() ?? 'the latest available release';

        return [
            'versionExist' => true,
            'msg' => sprintf('The latest version of software is %s', $latestVersion),
            'link' => (string) $version->getLink(),
            'st' => $hardware['st'] ? (string) ($version->getSt() ?? '') : '',
            'gd' => $hardware['gd'] ? (string) ($version->getGd() ?? '') : '',
        ];
    }

    /**
     * @return array{st: bool, gd: bool, isLci: bool, lciHwType: ?string}|null
     */
    private function detectHardware(string $hardwareVersion): ?array
    {
        $patterns = [
            ['pattern' => '/^CPAA_[0-9]{4}\.[0-9]{2}\.[0-9]{2}(_[A-Z]+)?$/i', 'st' => true, 'gd' => false, 'isLci' => false, 'lciHwType' => null],
            ['pattern' => '/^CPAA_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}(_[A-Z]+)?$/i', 'st' => false, 'gd' => true, 'isLci' => false, 'lciHwType' => null],
            ['pattern' => '/^B_C_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i', 'st' => true, 'gd' => false, 'isLci' => true, 'lciHwType' => 'CIC'],
            ['pattern' => '/^B_N_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i', 'st' => false, 'gd' => true, 'isLci' => true, 'lciHwType' => 'NBT'],
            ['pattern' => '/^B_E_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i', 'st' => false, 'gd' => true, 'isLci' => true, 'lciHwType' => 'EVO'],
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern['pattern'], $hardwareVersion) === 1) {
                return [
                    'st' => $pattern['st'],
                    'gd' => $pattern['gd'],
                    'isLci' => $pattern['isLci'],
                    'lciHwType' => $pattern['lciHwType'],
                ];
            }
        }

        return null;
    }

    /**
     * @param array{st: bool, gd: bool, isLci: bool, lciHwType: ?string} $hardware
     */
    private function isCompatible(FirmwareVersion $version, array $hardware): bool
    {
        $name = (string) $version->getName();
        $isLciEntry = str_starts_with(mb_strtolower($name), 'lci');

        if ($hardware['isLci'] !== $isLciEntry) {
            return false;
        }

        if ($hardware['isLci'] && $hardware['lciHwType'] !== null) {
            return stripos($name, $hardware['lciHwType']) !== false;
        }

        return true;
    }

    /**
     * @return array{versionExist: bool, msg: string, link: string, st: string, gd: string}
     */
    private function error(string $message): array
    {
        return [
            'versionExist' => false,
            'msg' => $message,
            'link' => '',
            'st' => '',
            'gd' => '',
        ];
    }
}
