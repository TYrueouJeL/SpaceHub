# Admin Dashboard — SpaceHub

Objectif  
Fournir aux administrateurs une vue synthétique et actionnable des indicateurs clés (KPI), tendances, alertes et listes opérationnelles pour gérer les lieux, réservations, paiements et avis.

## Priorité MVP
- KPI cards : utilisateurs, lieux, réservations, revenus
- Réservations récentes (liste)
- Top lieux (revenus / réservations / note)
- Calendrier de disponibilités (FullCalendar)

## Principaux KPI (Vue globale)
- Nombre total d'utilisateurs — `User::count` (ou `UserRepository`)
- Nouveaux utilisateurs (aujourd'hui / 7j / 30j)
- Nombre total de lieux — `Place::count`
- Réservations : total / aujourd'hui / semaine / mois — filtrer via `ReservationRepository`
- Revenus : total / période — si montant stocké dans `Reservation` (`amount`, `paymentStatus`)
- Taux d'occupation global (période) — calcul via `Reservation` vs capacité des `Place`
- Note moyenne des lieux — `ReviewRepository::getAverageRating` ou agrégation DQL
- Réservations en attente / statut paiement (`pending`, `confirmed`, `failed`)

## Tendances / séries temporelles
- Réservations par jour / semaine (graph line)
- Revenus par jour / mois (bar/line)
- Nouveaux utilisateurs par période

## Top / classements
- Top 10 lieux par revenus (`ReservationRepository::getTopPlacesByRevenue`)
- Top 10 lieux par nombre de réservations
- Top lieux par note moyenne (`ReviewRepository::getTopRatedPlaces`)

## Alertes & activité récente
- Réservations récentes (liste avec liens d'action)
- Paiements échoués / sessions Stripe en `pending`
- Nouveaux avis en attente de modération
- Uploads récents d'images / erreurs d'upload

## Listes opérationnelles (filtrables)
- Liste des réservations (filtres : lieu, user, date, statut)
- Liste des lieux (capacité, prix, type)
- Équipements par lieu (`PlaceEquipement` / `Equipment`)

## Visualisations cartographiques & calendrier
- Carte interactive des lieux (GeoJSON) avec clustering — endpoint `GET /api/places.geojson`
- Calendrier (FullCalendar) des réservations par lieu — endpoint JSON `GET /places/{id}/events`

## Indicateurs d'usage & performance
- Taux d'occupation par `Place` et par `PlaceType`
- Taux de conversion (visite → réservation)
- Temps moyen entre réservation et début

## Actions admin & exports
- Export CSV / iCal pour réservations (`ReservationController::exportCsv`, `exportIcal`)
- Filtre date global
- Actions rapides : valider / annuler réservation, rembourser, modérer avis

## Sécurité & autorisations
- Restreindre la route à `ROLE_ADMIN` (vérification déjà présente dans `src/Controller/AdminController.php`)
- Journaux pour actions critiques
- CSRF pour actions AJAX modifiant l'état

## Sources / méthodes recommandées (repositories & services)
- `src/Entity/Reservation.php` : `startDate`, `endDate`, `user`, `place` — ajouter si nécessaire `amount`, `paymentStatus`, `stripeSessionId`
- `src/Entity/Place.php` : `capacity`, `price`, `type`, `placeEquipements`, `reviews`
- `src/Entity/User.php` : `email`, `roles`, date de création
- `src/Entity/Review.php` : `rating`, `createdAt`
- Repositories utiles :
    - `ReservationRepository::getRevenueByPeriod(DateTime $from, DateTime $to)`
    - `ReservationRepository::countByPeriod(...)`
    - `PlaceRepository::getTopPlacesByRevenue(...)`
    - `ReviewRepository::getAverageRating(Place $place)`
- Service recommandé : `AnalyticsService` pour préparer séries temporelles et caches

## Implémentation rapide côté backend
- Ajouter méthodes d'agrégation dans les repositories (QueryBuilder / DQL) pour éviter calculs en contrôleur.
- Cacher les calculs lourds (Symfony Cache / Redis) et invalider sur changement de réservation/payment.
- Endpoints JSON pour charts et FullCalendar (contrôles d'accès `ROLE_ADMIN`/propriétaire).

## Modifications suggérées dans le projet actuel
- Compléter `src/Controller/AdminController.php` pour injecter les repositories nécessaires et passer les données au template `templates/admin/index.html.twig`.
- Rendre `templates/admin/index.html.twig` (ou `templates/admin/dashboard.html.twig`) contenant :
    - KPI cards
    - Graphs (Chart.js)
    - Tableau paginé (DataTables ou simple pagination backend)
    - Calendrier FullCalendar
    - Carte Leaflet si `latitude`/`longitude` ajoutés à `Place`
- Ajouter champs optionnels si manquent : `Reservation.amount`, `Reservation.paymentStatus`, `Place.latitude`, `Place.longitude`.

## Tests & qualité
- Tests unitaires des méthodes d'agrégation en repository
- Tests fonctionnels pour la route admin (accès restreint)
- Tests d'API JSON (structure renvoyée pour charts / calendar)

## Checklist avant livraison
- [ ] Méthodes d'agrégation testées
- [ ] Templates frontend avec assets compilés (`npm run build`)
- [ ] Routes protégées (`ROLE_ADMIN`)
- [ ] Exports CSV/iCal testés
- [ ] Cache et invalidation mis en place pour calculs lourds

---

Fichiers mentionnés pour intégration :
- `src/Controller/AdminController.php` (compléter)
- `templates/admin/index.html.twig` ou `templates/admin/dashboard.html.twig` (créer)
- `src/Repository/ReservationRepository.php`, `PlaceRepository.php`, `ReviewRepository.php`
- `src/Service/AnalyticsService.php` (optionnel)
