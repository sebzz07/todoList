<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route(path: '/users', name: 'user_list', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être Administrateur pour accéder à cette page')]
    public function listAction(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', ['users' => $userRepository->findAll()]);
    }

    #[Route(path: '/users/create', name: 'user_create')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être Administrateur pour accéder à cette page')]
    public function createAction(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): RedirectResponse|Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            ));

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/users/{id}/edit', name: 'user_edit')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être Administrateur pour accéder à cette page')]
    public function editAction(User $user, UserRepository $userRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher): RedirectResponse|Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            ));
            $user->setRoles([$form->get('role')->getData()]);

            $userRepository->add($user, true);

            $this->addFlash('success', "L'utilisateur a bien été modifié.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
