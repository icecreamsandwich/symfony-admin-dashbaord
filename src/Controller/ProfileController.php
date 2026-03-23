<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = (string) $form->get('currentPassword')->getData();
            $newPassword = (string) $form->get('newPassword')->getData();
            $confirmPassword = (string) $form->get('confirmPassword')->getData();

            if ($newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '') {
                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $form->get('currentPassword')->addError(new FormError('Current password is incorrect.'));
                }

                if ($newPassword === '') {
                    $form->get('newPassword')->addError(new FormError('Enter a new password to continue.'));
                }

                if ($newPassword !== $confirmPassword) {
                    $form->get('confirmPassword')->addError(new FormError('Password confirmation does not match.'));
                }

                if ($form->isValid()) {
                    $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                }
            }

            if ($form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', 'Profile updated.');

                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form,
        ]);
    }
}
