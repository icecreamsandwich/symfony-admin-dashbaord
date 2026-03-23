<?php

namespace App\Controller\Admin;

use App\Entity\FirmwareVersion;
use App\Form\FirmwareVersionType;
use App\Repository\FirmwareVersionRepository;
use App\Service\FirmwareVersionJsonStore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/firmware')]
#[IsGranted('ROLE_ADMIN')]
class FirmwareVersionController extends AbstractController
{
    #[Route('', name: 'app_admin_firmware_index', methods: ['GET'])]
    public function index(
        FirmwareVersionRepository $firmwareVersionRepository,
        FirmwareVersionJsonStore $jsonStore,
    ): Response {
        $jsonStore->initializeFromJsonIfEmpty();

        return $this->render('admin/firmware/index.html.twig', [
            'versions' => $firmwareVersionRepository->findAllOrdered(),
            'json_path' => $jsonStore->getJsonPath(),
        ]);
    }

    #[Route('/new', name: 'app_admin_firmware_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FirmwareVersionRepository $firmwareVersionRepository,
        FirmwareVersionJsonStore $jsonStore,
    ): Response {
        $jsonStore->initializeFromJsonIfEmpty();

        $firmwareVersion = new FirmwareVersion();
        $form = $this->createForm(FirmwareVersionType::class, $firmwareVersion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($firmwareVersion->isLatest() && $firmwareVersion->getName() !== null) {
                $firmwareVersionRepository->clearLatestForName($firmwareVersion->getName());
            }

            $entityManager->persist($firmwareVersion);
            $entityManager->flush();
            $jsonStore->exportAllToJson();

            $this->addFlash('success', 'Firmware version created and exported to JSON.');

            return $this->redirectToRoute('app_admin_firmware_index');
        }

        return $this->render('admin/firmware/form.html.twig', [
            'title' => 'Add Firmware Version',
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_firmware_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        FirmwareVersion $firmwareVersion,
        EntityManagerInterface $entityManager,
        FirmwareVersionRepository $firmwareVersionRepository,
        FirmwareVersionJsonStore $jsonStore,
    ): Response {
        $jsonStore->initializeFromJsonIfEmpty();

        $form = $this->createForm(FirmwareVersionType::class, $firmwareVersion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($firmwareVersion->isLatest() && $firmwareVersion->getName() !== null) {
                $firmwareVersionRepository->clearLatestForName($firmwareVersion->getName(), $firmwareVersion->getId());
            }

            $entityManager->flush();
            $jsonStore->exportAllToJson();

            $this->addFlash('success', 'Firmware version updated and exported to JSON.');

            return $this->redirectToRoute('app_admin_firmware_index');
        }

        return $this->render('admin/firmware/form.html.twig', [
            'title' => 'Edit Firmware Version',
            'form' => $form,
            'firmware_version' => $firmwareVersion,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_firmware_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        FirmwareVersion $firmwareVersion,
        EntityManagerInterface $entityManager,
        FirmwareVersionJsonStore $jsonStore,
    ): Response {
        if ($this->isCsrfTokenValid('delete_firmware_'.$firmwareVersion->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($firmwareVersion);
            $entityManager->flush();
            $jsonStore->exportAllToJson();
            $this->addFlash('success', 'Firmware version deleted and JSON overwritten.');
        }

        return $this->redirectToRoute('app_admin_firmware_index');
    }
}
