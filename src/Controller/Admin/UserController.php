<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findBy([], ['email' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user, [
            'require_password' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyRoles($user, (bool) $form->get('isAdmin')->getData());
            $this->hashPassword($user, (string) $form->get('plainPassword')->getData(), $passwordHasher);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/form.html.twig', [
            'title' => 'Add User',
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $form = $this->createForm(AdminUserType::class, $user, [
            'require_password' => false,
        ]);
        $form->get('isAdmin')->setData(in_array('ROLE_ADMIN', $user->getRoles(), true));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyRoles($user, (bool) $form->get('isAdmin')->getData());

            $plainPassword = (string) $form->get('plainPassword')->getData();
            if ($plainPassword !== '') {
                $this->hashPassword($user, $plainPassword, $passwordHasher);
            }

            $entityManager->flush();

            $this->addFlash('success', 'User updated.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/form.html.twig', [
            'title' => 'Edit User',
            'form' => $form,
            'managed_user' => $user,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid('delete_user_'.$user->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_user_index');
        }

        if ($this->getUser() instanceof User && $this->getUser()->getId() === $user->getId()) {
            $this->addFlash('success', 'You cannot delete the currently signed-in user.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User deleted.');

        return $this->redirectToRoute('app_admin_user_index');
    }

    private function applyRoles(User $user, bool $isAdmin): void
    {
        $user->setRoles($isAdmin ? ['ROLE_ADMIN'] : []);
    }

    private function hashPassword(User $user, string $plainPassword, UserPasswordHasherInterface $passwordHasher): void
    {
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
    }
}
