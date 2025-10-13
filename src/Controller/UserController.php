<?php

namespace App\Controller;

use App\Form\RegisterFormType;
use App\Repository\PlaceRepository;
use App\Repository\PlaceTypeRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    #[Route('/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/user.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/edit', name: 'app_user_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(RegisterFormType::class, $user);
        $form->handleRequest($request);

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashed);
            }

            $email = $form->get('email')->getData();
            if ($email) {
                $user->setEmail($email);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'editForm' => $form,
        ]);
    }

    #[Route('/user/reservations', name: 'app_user_reservations')]
    public function reservations(ReservationRepository $reservationRepository, PlaceTypeRepository $placeTypeRepository): Response
    {
        $user = $this->getUser();
        $placeTypes = $placeTypeRepository->findAll();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $reservations = $reservationRepository->findBy(['user' => $user]);

        return $this->render('user/reservations.html.twig', [
            'user' => $user,
            'reservations' => $reservations,
            'placeTypes' => $placeTypes,
        ]);
    }
}
