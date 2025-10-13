<?php

namespace App\Controller;

use App\Repository\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(PlaceRepository $placeRepository): Response
    {
        $places = $placeRepository->createQueryBuilder('p')
            ->leftJoin('p.reservations', 'r')
            ->addSelect('COUNT(r) AS HIDDEN reservationsCount')
            ->groupBy('p.id')
            ->orderBy('reservationsCount', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        return $this->render('home/home.html.twig', [
            'places' => $places,
        ]);
    }
}
