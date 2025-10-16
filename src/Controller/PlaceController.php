<?php
// File: src/Controller/PlaceController.php
namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Review;
use App\Form\ReservationFormType;
use App\Form\ReviewFormType;
use App\Repository\EventTypeRepository;
use App\Repository\PlaceEquipementRepository;
use App\Repository\PlaceRepository;
use App\Repository\PlaceTypeRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlaceController extends AbstractController
{
    #[Route('/place', name: 'app_place')]
    public function index(Request $request, PlaceRepository $placeRepository, PlaceTypeRepository $placeTypeRepository): Response
    {
        $perPage = 6;
        $page = max(1, (int) $request->query->get('page', 1));
        $currentFilter = $request->query->get('filterType', '');

        // Query builder for places with optional filter by place type name
        $qb = $placeRepository->createQueryBuilder('p')
            ->leftJoin('p.type', 't')
            ->addSelect('t')
            ->orderBy('p.id', 'ASC');

        if ($currentFilter !== '') {
            $qb->andWhere('t.name = :typeName')
                ->setParameter('typeName', $currentFilter);
        }

        // Count total results
        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(p.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = (int) max(1, ceil($total / $perPage));

        // Fetch paginated results
        $places = $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        $placetypes = $placeTypeRepository->findAll();

        return $this->render('place/place.html.twig', [
            'places' => $places,
            'placetypes' => $placetypes,
            'currentFilter' => $currentFilter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/place/{id}', name: 'app_place_detail')]
    public function detail(int $id, EventTypeRepository $eventTypeRepository, PlaceRepository $placeRepository, PlaceEquipementRepository $placeEquipementRepository, ReviewRepository $reviewRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $place = $placeRepository->find($id);
        $reviewForm = null;
        $reviewFormView = null;
        $reviewStatus = 'not_logged_in';

        if (!$place) {
            throw $this->createNotFoundException('Le lieu demandé n\'existe pas.');
        }

        $placeEquipements = $placeEquipementRepository->findBy(['place' => $place]);

        $equipments = [];
        foreach ($placeEquipements as $pe) {
            $equipments[] = $pe->getEquipment();
        }

        $reviews = $reviewRepository->findBy(['place' => $place], ['createdAt' => 'DESC']);

        $user = $this->getUser();

        if ($user) {
            $existingReview = $reviewRepository->findOneBy(['place' => $place, 'user' => $user]);
            if ($existingReview) {
                $reviewStatus = 'already_reviewed';
            } else {
                $reviewStatus = 'can_review';
                $reviewForm = $this->createForm(ReviewFormType::class);
                $reviewForm->handleRequest($request);

                if ($reviewForm instanceof FormInterface && $reviewForm->isSubmitted() && $reviewForm->isValid()) {
                    $review = new Review();
                    $review->setRating($reviewForm->get('rating')->getData());
                    $review->setComment($reviewForm->get('comment')->getData());
                    $review->setPlace($place);
                    $review->setUser($user);
                    $review->setCreatedAt(new \DateTimeImmutable());

                    $entityManager->persist($review);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_place_detail', ['id' => $id]);
                }

                if ($reviewForm instanceof FormInterface) {
                    $reviewFormView = $reviewForm->createView();
                }

            }
        } else {
            $reviewStatus = 'not_logged_in';
        }

        $averageReviewRating = $reviewRepository->getAverageNoteByPlaceId($place->getId());
        $reviewNumber = $reviewRepository->count(['place' => $place]);

        $eventTypes = $eventTypeRepository->findAll();

        $reservation = new Reservation();
        $reservationForm = $this->createForm(ReservationFormType::class, $reservation, ['place' => $place]);
        $reservationForm->handleRequest($request);

        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {
            $reservation->setPlace($place);
            $reservation->setUser($user);

            $startDate = $reservationForm->get('startDate')->getData();
            $endDate = $reservationForm->get('endDate')->getData();

            if ($startDate > $endDate) {

            }

            $reservation->setStartDate($startDate);
            $reservation->setEndDate($endDate);
            $reservation->setCreatedAt();
            $reservation->setEventType($reservationForm->get('eventType')->getData());
            $reservation->setPeopleNumber($reservationForm->get('peopleNumber')->getData());

            $dateDiff = $startDate->diff($endDate);
            $price = $place->getPrice() * $dateDiff->days;
            $reservation->setPrice($price);

            $entityManager->persist($reservation);
            $entityManager->flush();

        }

        $similarPlaces = $placeRepository->findBy(['type' => $place->getType()], ['id' => 'DESC'], 3);

        return $this->render('place/detail.html.twig', [
            'place' => $place,
            'equipments' => $equipments,
            'reviews' => $reviews,
            'reviewForm' => $reviewFormView,
            'reviewStatus' => $reviewStatus,
            'averageReviewRating' => $averageReviewRating ?? 0,
            'reviewNumber' => $reviewNumber,
            'eventTypes' => $eventTypes,
            'reservationForm' => $reservationForm,
            'similarPlaces' => $similarPlaces,
        ]);
    }
}
