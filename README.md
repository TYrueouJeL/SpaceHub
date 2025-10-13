# SpaceHub — README détaillé des nouvelles fonctionnalités

Ce document décrit, de manière détaillée et actionnable, l'ajout des fonctionnalités proposées pour le projet `SpaceHub`. Pour chaque fonctionnalité : description, entités/migrations, répertoire/repository, contrôleur/route, formulaires/Twig, assets JS/CSS, services, tests et commandes d'installation.

## Prérequis
- PHP 8.x, Composer
- Node.js & npm
- Base de données configurée (voir `config/packages/doctrine.yaml`)
- Serveur Symfony : `symfony server:start` ou `php -S localhost:8000 -t public`

## Installation initiale (rappels)
- Installer les dépendances PHP : `composer install`
- Installer les dépendances frontend : `npm install`
- Compiler les assets : `npm run dev` ou `npm run build`
- Exécuter les migrations : `bin/console doctrine:migrations:migrate`
- Charger les fixtures : `bin/console doctrine:fixtures:load`

---

## 1. Recherche avancée et filtres
- But : permettre recherche par type, équipements, capacité, prix, disponibilité, localisation.
- Entités / DB :
    - Requêtes et filtres via `PlaceRepository::search(array $criteria)` ; pas d'entité nouvelle.
- Repository :
    - Méthode DQL/QueryBuilder retournant résultats paginés.
- Formulaire :
    - `SearchFormType` (champs : `placeType`, `equipments[]`, `capacityMin`, `capacityMax`, `priceMin`, `priceMax`, `dateFrom`, `dateTo`, `q`).
- Contrôleur / Route :
    - `PlaceController::search(Request $request)` — route `place_search`.
- Template :
    - `templates/place/place.html.twig` : formulaire de filtre + affichage paginé.
- JS :
    - Améliorer `assets/app.js` pour envoyer requêtes AJAX si recherche dynamique.
- Tests :
    - Tests unitaires pour `PlaceRepository::search` et tests fonctionnels pour la page de recherche.
- Notes :
    - Gérer disponibilité via `ReservationRepository` pour exclure les lieux déjà réservés sur la fenêtre choisie.

---

## 2. Calendrier de disponibilités
- But : visualiser réservations et disponibilités par lieu (FullCalendar).
- Entités / DB :
    - Utilise `Reservation` existante ; prévoir champ `status` si pas présent (`PENDING`, `CONFIRMED`, `CANCELLED`).
- API / Endpoint :
    - JSON endpoint `GET /places/{id}/events` -> `PlaceController::calendarEvents` renvoie réservations en JSON (format FullCalendar).
- Vue / Contrôleur :
    - `PlaceController::calendar($id)` route `place_calendar` renvoie `templates/place/calendar.html.twig`.
- Frontend :
    - Ajouter FullCalendar via npm, intégrer dans `assets/app.js`.
- Tests :
    - Endpoint JSON testé (status, structure).
- Notes :
    - Autorisations : seuls propriétaires/admins peuvent voir toutes les réservations ; utilisateurs voient les leurs.

---

## 3. Paiement en ligne (Stripe)
- But : checkout sécurisé, gestion statuts de réservation.
- Dépendances :
    - PHP : `composer require stripe/stripe-php`
    - JS : `npm install @stripe/stripe-js` (optionnel)
- Env :
    - `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY` dans `.env.local`.
- Entités / DB :
    - Ajouter champs dans `Reservation` : `amount`, `paymentStatus` (`paid`, `pending`, `failed`), `stripeSessionId` (nullable).
- Service :
    - `Service/Payment/StripePaymentService.php` : création de session, verification webhook.
- Controller / Routes :
    - `CheckoutController::createSession` -> `checkout_create_session`
    - `CheckoutController::success` -> `checkout_success`
    - `CheckoutController::webhook` -> `checkout_webhook` (route publique, sécurisée par secret Stripe).
- Frontend :
    - Redirection vers Stripe Checkout ou intégration Elements.
- Sécurité :
    - Valider montants côté serveur, vérifier `stripe.signature` pour webhooks.
- Tests :
    - Tests unitaires pour `StripePaymentService` (mocks).
- Notes :
    - Gérer état réservation : bloquer place pendant `pending` puis confirmer après paiement.

---

## 4. Système d'avis et notes
- But : utilisateurs notent et commentent un `Place`.
- Entité :
    - Nouvelle entité `Review` : `id`, `user` (ManyToOne `User`), `place` (ManyToOne `Place`), `rating` (int 1-5), `comment`, `createdAt`.
- Formulaire :
    - `ReviewType` (rating, comment).
- Repository :
    - `ReviewRepository::findByPlaceWithPagination($place, $limit, $offset)` et `getAverageRating($place)`.
- Controller / Routes :
    - `ReviewController::create` (POST) -> `review_create`, et suppression/modération pour admin.
- Template :
    - `templates/place/detail.html.twig` : affichage des avis, moyenne, formulaire si utilisateur connecté.
- Tests :
    - Tests d'intégration création et affichage avis.
- Notes :
    - Empêcher multiples avis par utilisateur sur même place (unique constraint ou logique).

---

## 5. Favoris / Liste de souhaits
- But : utilisateurs sauvegardent des lieux.
- DB :
    - Relation ManyToMany `User` \<-\> `Place` via `favorites` (propriétés dans `User` ou via entité pivot `Favorite` si on veut historique).
- Controller / Routes :
    - `UserController::toggleFavorite($placeId)` -> route `favorite_toggle` (AJAX).
- Frontend :
    - Bouton favori (icône) qui appelle endpoint en POST/DELETE.
- Template :
    - Indiquer favoris dans `templates/place/place.html.twig` et `templates/place/detail.html.twig`.
- Tests :
    - Tests unitaires sur toggling, tests fonctionnels AJAX.
- Notes :
    - API sécurisée (CSRF token pour AJAX).

---

## 6. Galerie d'images et upload
- But : images multiples par `Place`, carousel UI.
- Entité :
    - `PlaceImage` : `id`, `place` (ManyToOne), `filename`, `position`, `uploadedAt`.
- Packages recommandés :
    - `composer require vich/uploader-bundle` ou utiliser `symfony/flysystem-bundle`.
- Formulaire :
    - Champ multiple d'upload dans `PlaceType` (admin) ou gestion via interface dédiée.
- Assets :
    - Intégrer un carousel JS (ex: Swiper) via npm.
- Templates :
    - `templates/place/detail.html.twig` : carousel principal + lightbox.
- Migrations / Stockage :
    - Stockage local `public/uploads/places/` ou remote (S3) via Flysystem.
- Tests :
    - Tests d'upload (mock filesystem).
- Notes :
    - Redimensionnement / optimisation lors de l'upload (liip/imagine ou packages d'images).

---

## 7. Notifications par e-mail et push
- But : mails de confirmation, rappels et notifications admin.
- Packages :
    - `symfony/mailer`
    - Optionnel : services push / pusher / mercure pour notifications temps réel.
- Templates :
    - `templates/emails/reservation_confirm.html.twig`, `reminder.html.twig`, etc.
- Services :
    - `NotificationService` pour encapsuler envoi mail + push.
- Événements :
    - Écouter `Reservation` via Doctrine events ou `Message`/`Messenger` pour envoi asynchrone.
- Cron / Rappels :
    - Commande `bin/console app:send-reminders` planifiée via cron ou scheduler.
- Tests :
    - Mailer tests (spool / mailer logger).
- Notes :
    - Prévoir textes multilingues si nécessaire.

---

## 8. Export / iCal
- But : exporter réservations au format CSV ou iCal.
- Controller / Route :
    - `ReservationController::exportCsv` -> route `reservation_export_csv`
    - `ReservationController::exportIcal` -> route `reservation_export_ical` (MIME `text/calendar`).
- Implémentation :
    - Générer flux CSV ou iCal à partir de `ReservationRepository::findByUser`.
- UI :
    - Bouton dans `templates/user/reservations.html.twig`.
- Tests :
    - Vérifier format CSV / iCal (entêtes et contenu).
- Notes :
    - Support d'export filtre (plages, lieu, statut).

---

## 9. Carte interactive (Leaflet / Google Maps)
- But : afficher lieux sur carte avec clustering et filtres.
- DB :
    - `Place` doit contenir `latitude`, `longitude`. Ajouter si absent.
- API :
    - Endpoint `GET /api/places.geojson` -> `PlaceController::geoJson` renvoie GeoJSON filtrable.
- Frontend :
    - Installer Leaflet (`npm install leaflet`) ou Google Maps.
    - Cluster via `leaflet.markercluster`.
- Templates :
    - Carte intégrée dans `templates/home/home.html.twig` ou page `place/index`.
- Tests :
    - Vérifier endpoint GeoJSON.
- Notes :
    - Géocodage automatisé via service (Nominatim / Google Geocoding) à l'enregistrement du lieu.

---

## 10. Dashboard admin & analytics
- But : statistiques (occupation, revenus, réservations).
- Controller / Route :
    - `AdminController::dashboard` -> route `admin_dashboard` (protéger via rôle `ROLE_ADMIN`).
- Requêtes :
    - Requêtes agrégées en `ReservationRepository` : taux d'occupation (`SUM(durations)/available_time`), revenus par période, top places.
- Templates :
    - `templates/admin/dashboard.html.twig` charts (Chart.js).
- Services :
    - `AnalyticsService` pour préparer les séries temporelles.
- Tests :
    - Tests de logique d'agrégation.
- Notes :
    - Pagination et cache des calculs coûteux (Redis ou cache Symfony).

---

## 11. Codes promo et réductions
- But : gérer codes promo pour réductions.
- Entité :
    - `PromoCode` : `id`, `code`, `type` (`percent`/`fixed`), `value`, `usageLimit`, `usedCount`, `validFrom`, `validUntil`, `active`.
- Validation :
    - Service `PromoService::applyCode(Reservation, string $code)` valide et applique réduction.
- Controller / Form :
    - Champ `promo_code` dans checkout, endpoint `promo_validate` pour check AJAX.
- Tests :
    - Tests unitaires pour application et limites d'utilisation.
- Notes :
    - Sécurité : éviter manipulations côté client, valider côté serveur.

---

## 12. Chat en temps réel (support)
- But : chat entre utilisateur et support ou propriétaire.
- Technologie :
    - WebSocket via Mercure ou Ratchet ; Mercure recommandé (Royalties intégrées Symfony).
- Entités :
    - Optionnel `MessageThread` et `Message` (author, content, readAt).
- Frontend :
    - JS pour abonnements Mercure, UI légère dans layout (floating).
- Controller / API :
    - Endpoints pour historique (`GET /api/threads/{id}/messages`) et pour initier thread.
- Tests :
    - Tests d'API et tests d'intégration message persisté.
- Notes :
    - Notifications push + badge non lus.

---

## Tâches communes / découpage par commits
- Chaque fonctionnalité : 1) entité/migration, 2) repository/service, 3) contrôleur/route, 4) formulaires/Twig, 5) JS/assets, 6) tests.
- Exemple de commande migration : `bin/console make:migration` puis `bin/console doctrine:migrations:migrate`.
- Branching : créer une branche par fonctionnalité (`feat/search`, `feat/stripe`, ...).

## Sécurité & bonnes pratiques
- Valider toutes les entrées côté serveur.
- Protéger routes sensibles par rôles (`ROLE_USER`, `ROLE_ADMIN`).
- Utiliser CSRF tokens pour formulaires et calls AJAX modifiant l'état.
- Logger erreurs de paiement et webhooks.
- Gérer sauvegarde et nettoyage des fichiers uploadés.

## Déploiement & variables d'environnement
- `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`
- Mailer config (`MAILER_DSN`)
- Flysystem/S3 credentials si stockage distant
- Configurer tâches cron pour rappel et jobs asynchrones

## Commandes utiles
- `composer install`
- `npm install`
- `npm run dev` / `npm run build`
- `bin/console doctrine:migrations:migrate`
- `bin/console doctrine:fixtures:load`
- `symfony server:start`

---

## Checklist avant merge d'une fonctionnalité
- [ ] Migration ajoutée et testée en local
- [ ] Tests unitaires et fonctionnels écrits
- [ ] Assets compilés
- [ ] Documentation mise à jour (`README.md` + commentaires)
- [ ] Contrôles de sécurité (auth, CSRF, validation) passés
- [ ] Revue de code effectuée

---

## Références structure projet
- Contrôleurs : `src/Controller/`
- Entités : `src/Entity/`
- Repositories : `src/Repository/`
- Templates : `templates/`
- Assets JS/CSS : `assets/` (`app.js`, `styles/app.css`)
- Migrations : `migrations/`

---

Ce `README.md` doit servir de feuille de route. Pour chaque fonctionnalité, créer une issue/PR dédiée et découper en tâches petites et testables.
