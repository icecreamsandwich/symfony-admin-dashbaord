<?php

namespace App\Controller;

use App\Service\FirmwareMatcher;
use App\Service\FirmwareVersionJsonStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FirmwareDownloadController extends AbstractController
{
    #[Route('/carplay/software-download', name: 'app_firmware_download_legacy_page', methods: ['GET', 'POST'])]
    #[Route('/download-firmware', name: 'app_firmware_download', methods: ['GET', 'POST'])]
    public function download(
        Request $request,
        FirmwareMatcher $firmwareMatcher,
        FirmwareVersionJsonStore $jsonStore,
    ): Response {
        $jsonStore->initializeFromJsonIfEmpty();

        $result = null;
        $formData = [
            'version' => '',
            'mcuVersion' => '',
            'hwVersion' => '',
        ];

        if ($request->isMethod('POST')) {
            $formData['version'] = trim((string) $request->request->get('version'));
            $formData['mcuVersion'] = trim((string) $request->request->get('mcuVersion'));
            $formData['hwVersion'] = trim((string) $request->request->get('hwVersion'));
            $result = $firmwareMatcher->match($formData['version'], $formData['hwVersion']);
        }

        return $this->render('firmware/download.html.twig', [
            'result' => $result,
            'form_data' => $formData,
        ]);
    }

    #[Route('/api2/carplay/software/version', name: 'app_api_firmware_version_legacy', methods: ['POST'])]
    #[Route('/api/firmware/software/version', name: 'app_api_firmware_version', methods: ['POST'])]
    public function apiVersion(
        Request $request,
        FirmwareMatcher $firmwareMatcher,
        FirmwareVersionJsonStore $jsonStore,
    ): JsonResponse {
        $jsonStore->initializeFromJsonIfEmpty();

        $payload = $request->request->all();
        if ($payload === []) {
            try {
                $payload = $request->toArray();
            } catch (\JsonException) {
                $payload = [];
            }
        }

        return new JsonResponse(
            $firmwareMatcher->match(
                $payload['version'] ?? null,
                $payload['hwVersion'] ?? null,
            ),
        );
    }
}
