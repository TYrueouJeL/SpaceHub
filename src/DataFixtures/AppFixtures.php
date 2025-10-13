<?php

namespace App\DataFixtures;

use App\Entity\Review;
use App\Entity\User;
use App\Entity\Reservation;
use App\Entity\PlaceType;
use App\Entity\Place;
use App\Entity\Equipment;
use App\Entity\PlaceEquipement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory as FakerFactory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Générer quelques PlaceType cohérents
        $typesData = [
            ['name' => 'Salle de réunion', 'description' => 'Salle équipée pour réunions et présentations.'],
            ['name' => 'Open-space', 'description' => 'Espace de travail partagé, adapté aux équipes.'],
            ['name' => 'Bureau privé', 'description' => 'Bureau fermé pour 1 à 3 personnes.'],
            ['name' => 'Salle de formation', 'description' => 'Salle avec chaises et vidéoprojecteur pour formations.'],
            ['name' => 'Espace événementiel', 'description' => 'Grand espace modulable pour événements et conférences.'],
        ];

        $placeTypes = [];
        foreach ($typesData as $data) {
            $pt = new PlaceType();
            $pt->setName($data['name']);
            $pt->setDescription($data['description']);
            $manager->persist($pt);
            $placeTypes[] = $pt;
        }

        // Générer des Equipment
        $equipmentNames = [
            'Projecteur',
            'Tableau blanc',
            'Vidéo-conférence',
            'Microphone',
            'Chaises supplémentaires',
            'Paperboard',
            'Écran',
            'Climatisation',
            'Haut-parleurs',
        ];

        $equipments = [];
        foreach ($equipmentNames as $name) {
            $e = new Equipment();
            $e->setName($name);
            $manager->persist($e);
            $equipments[] = $e;
        }

        // Générer des Places et leur associer un PlaceType + équipements
        $places = [];
        $placesCount = 20;
        for ($p = 0; $p < $placesCount; $p++) {
            $place = new Place();
            $place->setName($faker->company . ' - ' . $faker->word);
            $place->setAddress($faker->address);
            $place->setCapacity($faker->numberBetween(1, 100));
            $place->setDescription($faker->sentence(8));
            $place->setPrice($faker->numberBetween(30, 800));
            $place->setType($placeTypes[array_rand($placeTypes)]);

            $manager->persist($place);
            $places[] = $place;

            // Associer 1 à 4 équipements distincts par place
            $assigned = [];
            $equipCount = rand(1, 4);
            for ($i = 0; $i < $equipCount; $i++) {
                $eq = $equipments[array_rand($equipments)];
                if (in_array(spl_object_hash($eq), $assigned, true)) {
                    continue;
                }
                $assigned[] = spl_object_hash($eq);

                $pe = new PlaceEquipement();
                // Utiliser les méthodes add pour maintenir les deux côtés de la relation
                $place->addPlaceEquipement($pe);
                $eq->addPlaceEquipement($pe);
                $manager->persist($pe);
            }
        }

        // Quelques rôles possibles
        $possibleRoles = [
            ['ROLE_USER'],
            ['ROLE_USER', 'ROLE_MANAGER'],
            ['ROLE_USER', 'ROLE_ADMIN'],
        ];

        // Générer 10 utilisateurs
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $email = $faker->unique()->safeEmail();
            $user->setEmail($email);
            $roles = $possibleRoles[array_rand($possibleRoles)];
            $user->setRoles($roles);

            // Mot de passe simple pour fixtures
            $plaintext = 'password';
            $hashed = $this->passwordHasher->hashPassword($user, $plaintext);
            $user->setPassword($hashed);

            $manager->persist($user);
            $users[] = $user;

            // Générer 1 à 5 réservations par utilisateur, liées à des places existantes
            $reservationsCount = rand(1, 5);
            for ($r = 0; $r < $reservationsCount; $r++) {
                $reservation = new Reservation();

                // Lier l'utilisateur
                $reservation->setUser($user);

                // Dates (utilise les setters existants dans l'entité Reservation)
                $start = $faker->dateTimeBetween('-30 days', '+30 days');
                $reservation->setStartDate($start);

                // endDate à quelques jours après startDate
                $end = (clone $start)->modify('+'.rand(1, 7).' days');
                $reservation->setEndDate($end);

                // Lier une place aléatoire
                $place = $places[array_rand($places)];
                $reservation->setPlace($place);

                // Ajouter la relation inverse si disponible
                if (method_exists($user, 'addReservation')) {
                    $user->addReservation($reservation);
                }
                if (method_exists($place, 'addReservation')) {
                    $place->addReservation($reservation);
                }

                $manager->persist($reservation);
            }
        }

        // Générer des Reviews pour les places (0 à 5 avis par place)
        if (!empty($users) && !empty($places)) {
            foreach ($places as $place) {
                $count = rand(0, 5);
                for ($i = 0; $i < $count; $i++) {
                    $review = new Review();
                    $review->setPlace($place);
                    $review->setUser($faker->randomElement($users));
                    $review->setRating($faker->numberBetween(1, 5));
                    $review->setComment($faker->paragraph());
                    if (method_exists($review, 'setCreatedAt')) {
                        $createdMutable = $faker->dateTimeBetween('-1 year', 'now');
                        $createdImmutable = \DateTimeImmutable::createFromMutable($createdMutable);
                        $review->setCreatedAt($createdImmutable);
                    }
                    $manager->persist($review);

                    // si l'entité Place ou User a des méthodes d'ajout inverse, les appeler
                    if (method_exists($place, 'addReview')) {
                        $place->addReview($review);
                    }
                    if (method_exists($review->getUser(), 'addReview')) {
                        $review->getUser()->addReview($review);
                    }
                }
            }
        }

        $manager->flush();
    }
}
