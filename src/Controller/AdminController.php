<?php

namespace App\Controller;

use App\Repository\PlaceRepository;
use App\Repository\ReservationRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository, PlaceRepository $placeRepository, ReservationRepository $reservationRepository, ReviewRepository $reviewRepository): Response
    {
        $user = $this->getUser();

        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('app_login');
        }

        $totalUsers = $entityManager->getRepository('App\Entity\User')->count([]);

        $todayStart = new \DateTimeImmutable('today');
        $tomorrow = $todayStart->modify('+1 day');

        $weekStart = new \DateTimeImmutable('monday this week');
        $weekEnd = $weekStart->modify('+1 week');

        $monthStart = new \DateTimeImmutable('first day of this month');
        $monthEnd = $monthStart->modify('+1 month');

        $totalUsers = $userRepository->count([]);
        $newUsersToday = $userRepository->countBetween($todayStart, $tomorrow);
        $newUsersThisWeek = $userRepository->countBetween($weekStart, $weekEnd);
        $newUsersThisMonth = $userRepository->countBetween($monthStart, $monthEnd);

        $totalPlaces = $placeRepository->count([]);

        $totalReservations = $entityManager->getRepository('App\Entity\Reservation')->count([]);

        $newReservationsToday = $reservationRepository->countBetween($todayStart, $tomorrow);
        $newReservationsThisWeek = $reservationRepository->countBetween($weekStart, $weekEnd);
        $newReservationsThisMonth = $reservationRepository->countBetween($monthStart, $monthEnd);

        $totalIncome = $reservationRepository->sumTotalPrice();

        $incomeToday = $reservationRepository->sumTotalPriceBetween($todayStart, $tomorrow);
        $incomeThisWeek = $reservationRepository->sumTotalPriceBetween($weekStart, $weekEnd);
        $incomeThisMonth = $reservationRepository->sumTotalPriceBetween($monthStart, $monthEnd);

        $occupancyRateToday = $placeRepository->calculateOccupancyRate($todayStart, $tomorrow);
        $occupancyRateThisWeek = $placeRepository->calculateOccupancyRate($weekStart, $weekEnd);
        $occupancyRateThisMonth = $placeRepository->calculateOccupancyRate($monthStart, $monthEnd);

        $averageReviewNote = $reviewRepository->getAverageNote();

        return $this->render('admin/index.html.twig', [
            'totalUsers' => $totalUsers,
            'newUsersToday' => $newUsersToday,
            'newUsersThisWeek' => $newUsersThisWeek,
            'newUsersThisMonth' => $newUsersThisMonth,
            'totalPlaces' => $totalPlaces,
            'totalReservations' => $totalReservations,
            'newReservationsToday' => $newReservationsToday,
            'newReservationsThisWeek' => $newReservationsThisWeek,
            'newReservationsThisMonth' => $newReservationsThisMonth,
            'totalIncome' => $totalIncome,
            'incomeToday' => $incomeToday,
            'incomeThisWeek' => $incomeThisWeek,
            'incomeThisMonth' => $incomeThisMonth,
            'occupancyRateToday' => $occupancyRateToday,
            'occupancyRateThisWeek' => $occupancyRateThisWeek,
            'occupancyRateThisMonth' => $occupancyRateThisMonth,
            'averageReviewNote' => $averageReviewNote,
        ]);
    }
}
