# WypożyczalniaPRO – system wypożyczania sprzętu

> Webowa wypożyczalnia sprzętu (narzędzia, ogród, sport, elektronika).
> Projekt zaliczeniowy – kurs WdPAI, Politechnika Krakowska.

Minimalistyczny interfejs (biel + ciemna zieleń) z trybem jasnym i ciemnym,
zbudowany w czystym PHP 8.2 (OOP, bez frameworka) na PostgreSQL, uruchamiany
jednym poleceniem `docker compose up`.

---

## Spis treści

1. [Opis aplikacji](#opis-aplikacji)
2. [Technologie i architektura](#technologie-i-architektura)
3. [Instrukcja uruchomienia](#instrukcja-uruchomienia)
4. [Zmienne środowiskowe](#zmienne-środowiskowe)
5. [Konta testowe](#konta-testowe)
6. [Endpointy](#endpointy-srcroutesphp)
7. [Flow aplikacji](#flow-aplikacji)
8. [Schemat bazy danych](#schemat-bazy-danych)
9. [Elementy bazy danych](#elementy-bazy-danych)
10. [Bezpieczeństwo – Security Bingo](#bezpieczeństwo--security-bingo)
11. [Widoki aplikacji](#widoki-aplikacji)
12. [Scenariusz testowy](#scenariusz-testowy)
13. [Uruchamianie testów](#uruchamianie-testów)
14. [Checklista wymagań](#checklista-wymagań)

---

## Opis aplikacji

**WypożyczalniaPRO** to aplikacja webowa do obsługi wypożyczalni sprzętu.
Goście przeglądają katalog z wyszukiwarką, zalogowani klienci składają
wypożyczenia (z interaktywnym kalendarzem wyboru dat), a personel i administrator
zarządzają sprzętem, wypożyczeniami oraz użytkownikami.

### Główne funkcje wg roli

| Rola | Dostępne funkcje |
|---|---|
| **Gość** | Przeglądanie katalogu, dynamiczne wyszukiwanie (Fetch API), filtrowanie po kategorii, szczegóły sprzętu |
| **Klient** | Rejestracja, logowanie, wypożyczanie sprzętu (kalendarz dat), historia „Moje wypożyczenia", zgłoszenie zwrotu |
| **Pracownik** | Podgląd wszystkich wypożyczeń, oznaczanie zwrotów |
| **Administrator** | Wszystko co pracownik + CRUD sprzętu (wgrywanie i usuwanie zdjęć), zarządzanie użytkownikami i ich rolami |

Administrator nie może zmienić własnej roli ani usunąć własnego konta (blokada
po stronie serwera i ukrycie kontrolek w panelu). Usunięcie sprzętu lub
użytkownika powiązanego z wypożyczeniami jest blokowane czytelnym komunikatem
(zamiast błędu klucza obcego).

---

## Technologie i architektura

### Stack technologiczny

| Warstwa | Technologia |
|---|---|
| Backend | PHP 8.2 OOP (bez frameworka) |
| Baza danych | PostgreSQL 15 |
| Serwer HTTP | Nginx (reverse proxy → PHP-FPM) |
| Konteneryzacja | Docker + Docker Compose |
| Frontend | HTML5, CSS3 (zmienne CSS, media queries), Vanilla JavaScript (Fetch API) |
| Testy | PHPUnit 10, Bash + curl (testy integracyjne) |
| Autoloader | Composer (PSR-4) + własny autoloader jako fallback |

### Architektura MVC + Service + Repository

```
┌─────────────────────────────────────────────────────────┐
│                      PRZEGLĄDARKA                       │
│   HTML5 + CSS (tryb jasny/ciemny) + JavaScript          │
│   Fetch API (wyszukiwarka, kalendarz dat)               │
└──────────────────────────┬──────────────────────────────┘
                           │ HTTP (port 8080)
┌──────────────────────────▼──────────────────────────────┐
│                         NGINX                           │
│              reverse proxy → PHP-FPM                     │
└──────────────────────────┬──────────────────────────────┘
                           │ FastCGI (port 9000)
┌──────────────────────────▼──────────────────────────────┐
│                      PHP-FPM 8.2                        │
│  ┌──────────────────────────────────────────────────┐   │
│  │  Router  (Method + URI → Controller@Action)      │   │
│  │  obsługa 404 / 405 (metoda niedozwolona)         │   │
│  └────────────────────┬─────────────────────────────┘   │
│  ┌────────────────────▼─────────────────────────────┐   │
│  │  Middleware                                      │   │
│  │  CsrfMiddleware (globalny, POST) │ AuthMiddleware │   │
│  │  RoleMiddleware (admin / pracownik)              │   │
│  └────────────────────┬─────────────────────────────┘   │
│  ┌────────────────────▼─────────────────────────────┐   │
│  │  Controllers                                     │   │
│  │  Home │ Auth │ Equipment │ Rental │ User         │   │
│  └────────┬──────────────────────┬──────────────────┘   │
│           │    Services          │    Views (szablony PHP)│
│  ┌────────▼────────┐   ┌─────────▼────────────────────┐ │
│  │ AuthService     │   │ layout.php + home/ auth/      │ │
│  │ RentalService   │   │ equipment/ rentals/ admin/    │ │
│  │ LoginThrottle   │   │ errors/                       │ │
│  └────────┬────────┘   └──────────────────────────────┘ │
│  ┌────────▼────────────────────────────────────────────┐ │
│  │  Repositories (AbstractRepository + PDO)           │ │
│  │  User (Singleton) │ Equipment │ Rental │ Category   │ │
│  │  EquipmentImage │ LoginAttempt                      │ │
│  └────────┬────────────────────────────────────────────┘ │
│  ┌────────▼────────────────────────────────────────────┐ │
│  │  Models (readonly DTO)                             │ │
│  │  User │ Equipment │ Category │ Rental              │ │
│  └────────┬────────────────────────────────────────────┘ │
└───────────┼─────────────────────────────────────────────┘
            │ PDO (prepared statements)
┌───────────▼─────────────────────────────────────────────┐
│                    PostgreSQL 15                         │
│  Tabele:  users, user_profiles, categories, equipment,  │
│           equipment_images, rentals, rental_items,      │
│           login_attempts                                │
│  Widoki:  v_active_rentals, v_popular_equipment         │
│  Funkcja: fn_calculate_rental_cost                      │
│  Trigger: rental_items_after_insert                     │
│           (trg_decrement_available)                     │
└─────────────────────────────────────────────────────────┘
```

### Struktura katalogów

```
Projekt WdPAI/
├── database/
│   ├── schema.sql              # Tabele + ograniczenia + login_attempts
│   ├── views.sql               # Widoki v_active_rentals, v_popular_equipment
│   ├── triggers.sql            # Trigger + funkcja triggera
│   ├── functions.sql           # fn_calculate_rental_cost
│   ├── seed.sql                # Dane przykładowe (konta, sprzęt)
│   ├── install.sql             # Skrypt łączący całość (psql -f)
│   └── migrations/             # Migracje przyrostowe (login_attempts)
├── docs/
│   ├── erd.svg                 # Diagram ERD (źródło: erd.drawio, opis: erd.md)
│   ├── architecture.svg        # Diagram warstwowy
│   └── screenshots/            # Zrzuty (web + mobile, jasny + ciemny)
├── docker/
│   ├── php/Dockerfile          # PHP-FPM + pdo_pgsql
│   └── nginx/default.conf      # Konfiguracja Nginx
├── src/
│   ├── Controllers/            # AbstractController + Home/Auth/Equipment/Rental/User
│   ├── Core/                   # Autoloader, Config, Database, Router, Request,
│   │                           # Response, View, Session, Csrf, ErrorHandler
│   ├── Middleware/             # AuthMiddleware, RoleMiddleware, CsrfMiddleware
│   ├── Models/                 # User, Equipment, Category, Rental (readonly DTO)
│   ├── Repositories/           # AbstractRepository + 6 repozytoriów
│   ├── Services/               # AuthService, RentalService, LoginThrottle
│   ├── bootstrap.php           # Autoloader + .env + ErrorHandler
│   └── routes.php              # Definicje tras
├── views/                      # Szablony PHP (layout.php + sekcje)
├── public/
│   ├── css/style.css           # System wizualny + tryb jasny/ciemny
│   ├── js/search.js            # Wyszukiwarka (Fetch API)
│   ├── js/calendar.js          # Kalendarz wyboru dat
│   ├── js/theme.js             # Przełącznik motywu
│   ├── uploads/                # Wgrane zdjęcia sprzętu
│   └── index.php               # Front controller
├── tests/
│   ├── Unit/                   # UserModelTest, AuthServiceTest,
│   │                           # LoginThrottleTest, CsrfTest
│   ├── integration/smoke.sh    # Testy endpointów (curl)
│   └── bootstrap.php
├── .env.example
├── composer.json
├── phpunit.xml
└── docker-compose.yml
```

---

## Instrukcja uruchomienia

### Wymagania

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Git

### 1. Klonowanie repozytorium

```bash
git clone https://github.com/JakubSosnowsk737/WdPAI-Equipment_Rental_App.git
cd WdPAI-Equipment_Rental_App
```

### 2. Konfiguracja środowiska

```bash
cp .env.example .env
# domyślne wartości działają od razu
```

### 3. Uruchomienie

```bash
docker compose up -d --build
```

Przy pierwszym uruchomieniu Docker:
- buduje obraz PHP (z `pdo_pgsql`),
- inicjalizuje bazę danych skryptami z `database/` (montowane do
  `docker-entrypoint-initdb.d`: schema → views → triggers → functions → seed).

**Aplikacja dostępna pod:** `http://localhost:8080`
**PostgreSQL:** `localhost:5432`

### 4. Testy w kontenerze

```bash
docker compose exec php composer install     # raz, instaluje PHPUnit
docker compose exec php vendor/bin/phpunit --testdox
bash tests/integration/smoke.sh
```

### 5. Restart z czystą bazą danych

```bash
docker compose down -v   # usuwa wolumen (reset bazy)
docker compose up -d
```

### 6. Zatrzymanie

```bash
docker compose down
```

---

## Zmienne środowiskowe

Plik `.env` (wzorzec w `.env.example`):

| Zmienna | Opis | Wartość domyślna |
|---|---|---|
| `APP_NAME` | Nazwa aplikacji | `WypozyczalniaPRO` |
| `APP_ENV` | Środowisko | `dev` |
| `APP_DEBUG` | Tryb debugowania (stack trace) | `true` |
| `DB_HOST` | Host bazy danych | `db` |
| `DB_PORT` | Port PostgreSQL | `5432` |
| `DB_NAME` | Nazwa bazy | `wypozyczalnia` |
| `DB_USER` | Użytkownik DB | `app` |
| `DB_PASSWORD` | Hasło DB | `app_secret` |
| `SESSION_NAME` | Nazwa ciasteczka sesji | `wpro_sid` |
| `SESSION_LIFETIME` | Czas życia sesji (s) | `3600` |
| `SESSION_SECURE` | Flaga `Secure` na cookie (true dla HTTPS) | `false` |

> Na produkcji ustaw `APP_DEBUG=false` (ukrywa stack trace) oraz
> `SESSION_SECURE=true` (gdy aplikacja działa po HTTPS).

---

## Konta testowe

Hasła z `database/seed.sql` (przechowywane jako hash bcrypt):

| E-mail | Hasło | Rola |
|---|---|---|
| `admin@wpro.pl` | `admin123` | administrator |
| `pracownik@wpro.pl` | `pracownik123` | pracownik |
| `klient@wpro.pl` | `klient123` | klient |
| `klient2@wpro.pl` | `klient123` | klient |

---

## Endpointy (`src/routes.php`)

Routing zdefiniowany w `src/routes.php`, front controller: `public/index.php`.
Wszystkie żądania modyfikujące stan (`POST`) przechodzą przez **globalny
`CsrfMiddleware`** – brak/niepoprawny token kończy się kodem `403`.

### Autentykacja

| Metoda | URL | Middleware | Akcja |
|--------|-----|-----------|-------|
| `GET`  | `/register` | — | Formularz rejestracji |
| `POST` | `/register` | CSRF | Rejestracja użytkownika (rola `klient`) |
| `GET`  | `/login` | — | Formularz logowania |
| `POST` | `/login` | CSRF | Logowanie (limit prób, audyt) |
| `GET`  | `/logout` | — | Wylogowanie, zniszczenie sesji |

### Sprzęt (publiczne)

| Metoda | URL | Middleware | Akcja |
|--------|-----|-----------|-------|
| `GET`  | `/` | — | Strona główna (hero) |
| `GET`  | `/equipment` | — | Katalog sprzętu z wyszukiwarką |
| `GET`  | `/api/equipment` | — | Wyszukiwanie sprzętu (JSON, Fetch API) |
| `GET`  | `/equipment/{id}` | — | Szczegóły sprzętu + podobny sprzęt |

### Wypożyczenia (zalogowani)

| Metoda | URL | Middleware | Akcja |
|--------|-----|-----------|-------|
| `GET`  | `/rentals/new?equipment_id={id}` | Auth | Formularz wypożyczenia (kalendarz dat) |
| `POST` | `/rentals` | Auth, CSRF | Utworzenie wypożyczenia (transakcja) |
| `GET`  | `/rentals/mine` | Auth | Moje wypożyczenia |
| `POST` | `/rentals/{id}/return` | Auth, CSRF | Zgłoszenie / oznaczenie zwrotu |

### Panel administratora (tylko admin)

| Metoda | URL | Middleware | Akcja |
|--------|-----|-----------|-------|
| `GET`  | `/admin/users` | Admin | Lista użytkowników |
| `POST` | `/admin/users/{id}/role` | Admin, CSRF | Zmiana roli użytkownika |
| `POST` | `/admin/users/{id}/delete` | Admin, CSRF | Usunięcie użytkownika |
| `GET`  | `/admin/equipment` | Admin | Zarządzanie sprzętem |
| `GET`  | `/admin/equipment/new` | Admin | Formularz dodawania sprzętu |
| `POST` | `/admin/equipment` | Admin, CSRF | Dodanie sprzętu |
| `GET`  | `/admin/equipment/{id}/edit` | Admin | Formularz edycji sprzętu |
| `POST` | `/admin/equipment/{id}` | Admin, CSRF | Aktualizacja sprzętu |
| `POST` | `/admin/equipment/{id}/delete` | Admin, CSRF | Usunięcie sprzętu (blokada przy powiązanych wypożyczeniach) |
| `POST` | `/admin/equipment/{id}/images` | Admin, CSRF | Wgranie zdjęcia sprzętu |
| `POST` | `/admin/equipment/{id}/images/{imageId}/delete` | Admin, CSRF | Usunięcie zdjęcia sprzętu |

### Panel personelu (admin lub pracownik)

| Metoda | URL | Middleware | Akcja |
|--------|-----|-----------|-------|
| `GET`  | `/admin/rentals` | Admin/Pracownik | Wszystkie wypożyczenia |

### Kody odpowiedzi HTTP

| Kod | Kiedy |
|-----|-------|
| `200` | Sukces |
| `302` | Przekierowanie (po logowaniu, akcji, braku sesji) |
| `401` | Błędne dane logowania |
| `403` | Brak uprawnień / niepoprawny token CSRF |
| `404` | Nieznana trasa lub nieistniejący zasób |
| `405` | Metoda niedozwolona dla istniejącej ścieżki |
| `422` | Błędy walidacji formularza |
| `429` | Zbyt wiele prób logowania (rate limiting) |
| `500` | Nieobsłużony wyjątek serwera |

---

## Flow aplikacji

### Przepływ wypożyczenia

```
┌─────────────────────────────────────────────────────────────────────┐
│                       PRZEPŁYW WYPOŻYCZENIA                         │
└─────────────────────────────────────────────────────────────────────┘

  [KLIENT]                                   [PRACOWNIK / ADMIN]

  1. Przegląda katalog (/equipment)
     └─ Wyszukiwarka (Fetch API), filtr kategorii

  2. Wchodzi w szczegóły (/equipment/{id})
     └─ Klika „Wypożycz teraz"

  3. Wybiera termin w kalendarzu (/rentals/new)
     └─ klik daty od → klik daty do
     └─ podgląd liczby dni + szac. kosztu na żywo

  4. Zatwierdza (POST /rentals)
     └─ transakcja SERIALIZABLE: blokada egzemplarza,
        utworzenie rentals + rental_items
     └─ trigger zmniejsza available_quantity
     Status: [NOWE]

  5. Odbiera sprzęt                          5. Widzi wypożyczenie w
                                                /admin/rentals

  6. Klika „Zwróć" na /rentals/mine
     └─ transakcja: przywrócenie ilości      6. Może też oznaczyć zwrot
     Status: [ZAKOŃCZONE]                        z panelu personelu
```

### Statusy wypożyczenia

Zdefiniowane w `rentals.status` (ograniczenie `CHECK`) i w modelu `Rental`:

| Status | Znaczenie |
|---|---|
| `nowe` | Wypożyczenie utworzone (stan początkowy) |
| `aktywne` | Sprzęt wydany klientowi |
| `zakonczone` | Sprzęt zwrócony (przywraca dostępność) |
| `anulowane` | Wypożyczenie anulowane |

> Zaimplementowany przepływ: `nowe` → (zwrot) → `zakonczone`. Pozostałe statusy
> są dostępne w modelu i schemacie bazy.

### Autoryzacja i middleware

```
  Żądanie HTTP
       │
       ▼
  ┌──────────────┐   POST + zły token   ┌───────────┐
  │ CsrfMiddleware├─────────────────────►│  403      │
  │  (globalny)  │                      └───────────┘
  └──────┬───────┘
         │ token OK / metoda GET
         ▼
  ┌──────────────┐    brak sesji        ┌─────────────────┐
  │ AuthMiddleware├─────────────────────►│ redirect /login │
  └──────┬───────┘                      └─────────────────┘
         │ sesja OK
         ▼
  ┌──────────────┐    zła rola          ┌───────────┐
  │ RoleMiddleware├─────────────────────►│  403      │
  └──────┬───────┘                      └───────────┘
         │ rola OK
         ▼
  Kontroler → Serwis → Repozytorium → PostgreSQL
```

---

## Schemat bazy danych

### Diagram ERD

![Diagram ERD](docs/erd.svg)

Źródło edytowalne: [`docs/erd.drawio`](docs/erd.drawio) · opis tekstowy: [`docs/erd.md`](docs/erd.md).

### Relacje między tabelami

```
users (1) ───── (1) user_profiles          ← jeden-do-jednego
  │
  │ (1:N)
  ▼
rentals (1) ──── (N) rental_items (N) ──── (1) equipment ──── (N:1) categories
                  [tabela łącząca M:N            │
                   z atrybutami quantity,        │ (1:N)
                   daily_rate]                   ▼
                                           equipment_images

login_attempts   ← audyt + rate limiting (bez kluczy obcych)
```

### Opis tabel

| Tabela | Opis | Klucze |
|---|---|---|
| `users` | Konta użytkowników | PK: id, UNIQUE: email |
| `user_profiles` | Profil (telefon, adres) – relacja 1:1 | PK/FK: user_id |
| `categories` | Kategorie sprzętu | PK: id, UNIQUE: name |
| `equipment` | Sprzęt do wypożyczenia | PK: id, FK: category_id |
| `equipment_images` | Zdjęcia sprzętu (1:N) | PK: id, FK: equipment_id |
| `rentals` | Nagłówek wypożyczenia | PK: id, FK: user_id |
| `rental_items` | Pozycje wypożyczenia (M:N) | PK: (rental_id, equipment_id) |
| `login_attempts` | Audyt prób logowania (rate limiting) | PK: id |

### Akcje na kluczach obcych

| Tabela | Klucz obcy | ON UPDATE | ON DELETE |
|---|---|---|---|
| `user_profiles` | user_id → users | CASCADE | CASCADE |
| `equipment` | category_id → categories | CASCADE | RESTRICT |
| `equipment_images` | equipment_id → equipment | CASCADE | CASCADE |
| `rentals` | user_id → users | CASCADE | RESTRICT |
| `rental_items` | rental_id → rentals | CASCADE | CASCADE |
| `rental_items` | equipment_id → equipment | CASCADE | RESTRICT |

Baza jest w **3NF** – brak redundancji oraz anomalii modyfikacji/usuwania.
Stawka `rental_items.daily_rate` jest kopiowana w momencie wypożyczenia
(odzwierciedla cenę z chwili transakcji, a nie aktualną), więc nie jest redundancją.

---

## Elementy bazy danych

### Widoki (2)

**`v_active_rentals`** – aktywne wypożyczenia z danymi klienta i sprzętu
(JOIN po 4 tabelach: `rentals` + `users` + `rental_items` + `equipment` + `categories`).
Filtruje statusy `nowe` i `aktywne`.

**`v_popular_equipment`** – ranking popularności sprzętu (JOIN + `LEFT JOIN` +
`GROUP BY`): liczba wypożyczeń i suma wypożyczonych sztuk per egzemplarz.

### Wyzwalacz (1)

**`rental_items_after_insert`** (funkcja `trg_decrement_available`) – po każdym
`INSERT` do `rental_items` automatycznie zmniejsza `equipment.available_quantity`
i przerywa transakcję wyjątkiem, gdy dostępność spadłaby poniżej zera.

### Funkcja (1)

**`fn_calculate_rental_cost(p_equipment_id, p_quantity, p_days) → NUMERIC(10,2)`**
– oblicza koszt wypożyczenia (stawka dzienna × ilość × liczba dni),
z walidacją parametrów.

### Transakcja

Tworzenie wypożyczenia (`RentalService::rent`) na poziomie izolacji
**SERIALIZABLE** – zapobiega podwójnemu wypożyczeniu tego samego egzemplarza:

```php
$pdo->exec('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
$pdo->beginTransaction();
// SELECT ... FOR UPDATE  (blokada egzemplarza)
// INSERT INTO rentals ...
// INSERT INTO rental_items ...  → trigger zmniejsza available_quantity
$pdo->commit();
// rollback w catch
```

Zwrot sprzętu (`RentalService::returnRental`) również działa w transakcji
(przywraca ilości i ustawia status `zakonczone`). Serwis waliduje także daty
po stronie serwera (format, brak dat z przeszłości, maks. 30 dni).

---

## Bezpieczeństwo – Security Bingo

Aplikacja realizuje **24 z 25** punktów „Security Bingo". Jedyny celowo
**niezaimplementowany** punkt to **E1 (wymuszenie HTTPS)** – aplikacja działa
lokalnie po HTTP, a obsługa flagi `Secure` na cookie jest przygotowana i
włączana zmienną `SESSION_SECURE=true`.

### Zaimplementowane zabezpieczenia

| Kategoria | Zabezpieczenie | Implementacja |
|---|---|---|
| **SQL Injection** | Prepared statements (PDO) | wszystkie repozytoria |
| **XSS** | Escaping outputu | `htmlspecialchars(..., ENT_QUOTES)` w widokach |
| **CSRF** | Token w każdym formularzu | `Csrf` + globalny `CsrfMiddleware`, `hash_equals()` |
| **Autentykacja** | Bcrypt | `password_hash()` / `password_verify()` |
| **Sesja** | Regeneracja ID po logowaniu | `session_regenerate_id(true)` |
| **Cookies** | HttpOnly + SameSite=Lax (+ Secure opcjonalnie) | `Session::start()` |
| **Autoryzacja** | Role admin / pracownik / klient | `RoleMiddleware` |
| **Rate limiting** | Blokada po 5 próbach / 15 min | `LoginThrottle` + tabela `login_attempts` |
| **Hasła** | Nigdy nie logowane | audyt loguje e-mail + IP, nie hasło |
| **Błędy** | Brak stack trace na produkcji | `ErrorHandler` + `APP_DEBUG` |
| **Generic errors** | Nie ujawnia, czy e-mail istnieje | jednakowy komunikat logowania |
| **Walidacja** | Server-side na wejściach | `AuthService`, `RentalService`, `filter_var`, limity długości |

### Plansza Security Bingo (24/25)

Legenda: ✅ zaimplementowane · ❌ celowo pominięte (HTTPS).

|   | A | B | C | D | E |
|:-:|---|---|---|---|---|
| **1** | ✅ **A1**<br>Ochrona przed SQL injection (prepared statements / brak konkatenacji SQL) | ✅ **B1**<br>Nie zdradzam, czy e-mail istnieje – komunikat „E-mail lub hasło niepoprawne" | ✅ **C1**<br>Walidacja formatu e-mail po stronie serwera | ✅ **D1**<br>UserRepository zarządzany jako singleton | ❌ **E1**<br>Logowanie i rejestracja tylko przez HTTPS |
| **2** | ✅ **A2**<br>login/register przyjmuje dane tylko na POST, GET renderuje widok | ✅ **B2**<br>CSRF token w formularzu logowania | ✅ **C2**<br>CSRF token w formularzu rejestracji | ✅ **D2**<br>Ograniczam długość wejścia (e-mail, hasło, imię…) | ✅ **E2**<br>Hasła przechowywane jako hash (bcrypt, `password_hash`) |
| **3** | ✅ **A3**<br>Hasła nigdy nie są logowane w logach / błędach | ✅ **B3**<br>Po poprawnym logowaniu regeneruję ID sesji | ✅ **C3**<br>Cookie sesyjne ma flagę `HttpOnly` | ✅ **D3**<br>Cookie sesyjne ma flagę `Secure` | ✅ **E3**<br>Cookie ma ustawione `SameSite` (Lax) |
| **4** | ✅ **A4**<br>Limit prób logowania / blokada czasowa po wielu próbach | ✅ **B4**<br>Waliduję złożoność hasła (min. długość itd.) | ✅ **C4**<br>Przy rejestracji sprawdzam, czy e-mail jest już w bazie | ✅ **D4**<br>Dane w widokach są escapowane (ochrona przed XSS) | ✅ **E4**<br>W produkcji nie pokazuję stack trace / surowych błędów |
| **5** | ✅ **A5**<br>Zwracam sensowne kody HTTP (400/401/403 przy błędach) | ✅ **B5**<br>Hasło nie jest przekazywane do widoków ani `echo`/`var_dump` | ✅ **C5**<br>Z bazy pobieram tylko minimalny zestaw danych o użytkowniku | ✅ **D5**<br>Mam poprawne wylogowanie – niszczę sesję użytkownika | ✅ **E5**<br>Loguję nieudane próby logowania (bez haseł) do audytu |

> **E1 (HTTPS)** jest jedynym celowo pominiętym polem – aplikacja działa lokalnie
> po HTTP. Obsługa flagi `Secure` na cookie jest jednak przygotowana w kodzie
> (`Session::start()`) i włączana zmienną `SESSION_SECURE=true`.

---

## Widoki aplikacji

Zrzuty ekranu w [`docs/screenshots/`](docs/screenshots) – wersja webowa i mobilna,
tryb jasny i ciemny.

### Motyw jasny vs ciemny (desktop)

| Tryb jasny | Tryb ciemny |
|---|---|
| ![Katalog jasny](docs/screenshots/catalog.png) | ![Katalog ciemny](docs/screenshots/catalog-dark.png) |
| ![Szczegóły jasny](docs/screenshots/equipment-detail.png) | ![Szczegóły ciemny](docs/screenshots/equipment-detail-dark.png) |
| ![Kalendarz jasny](docs/screenshots/rental-calendar.png) | ![Kalendarz ciemny](docs/screenshots/rental-calendar-dark.png) |

### Wersja mobilna (responsywny układ jednokolumnowy)

| Strona główna | Katalog (ciemny) |
|---|---|
| ![Mobile home](docs/screenshots/mobile-home.png) | ![Mobile katalog](docs/screenshots/mobile-catalog-dark.png) |

### Katalog sprzętu (`views/equipment/index.php`)

Siatka kart sprzętu (CSS Grid) z wyszukiwarką odświeżaną przez Fetch API.

```php
<article class="equipment-card">
    <h3><?= htmlspecialchars($eq->name, ENT_QUOTES) ?></h3>
    <p class="cat"><?= htmlspecialchars($eq->categoryName ?? '', ENT_QUOTES) ?></p>
    <p class="rate"><?= number_format($eq->dailyRate, 2) ?> zł / dzień</p>
    <p class="stock">Dostępne: <?= $eq->availableQuantity ?> / <?= $eq->totalQuantity ?></p>
    <a href="/equipment/<?= (int) $eq->id ?>" class="btn-sm">Szczegóły</a>
</article>
```

### Wyszukiwanie przez Fetch API (`public/js/search.js`)

```js
fetch('/api/equipment?' + params.toString(), { headers: { 'Accept': 'application/json' } })
    .then(r => r.ok ? r.json() : Promise.reject(r.status))
    .then(data => render(data.items || []));
```

### Logowanie z tokenem CSRF (`views/auth/login.php`)

```php
<form method="post" action="/login">
    <?= App\Core\Csrf::field() ?>
    <label>Adres e-mail<input type="email" name="email" required maxlength="150" autocomplete="email"></label>
    <label>Hasło<input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit" class="btn">Zaloguj się</button>
</form>
```

### Kalendarz wyboru dat (`views/rentals/new.php` + `public/js/calendar.js`)

Interaktywny kalendarz (vanilla JS) z wyborem zakresu, blokadą dat przeszłych
oraz podglądem liczby dni i szacowanego kosztu. Wpisuje wartości do natywnych
pól `type="date"` (fallback bez JS – progresywne ulepszanie).

```php
<div id="rental-calendar" class="calendar" data-rate="<?= $eq->dailyRate ?>"></div>
<input type="date" name="start_date" id="start_date" required>
<input type="date" name="end_date"   id="end_date"   required>
```

### Tryb jasny / ciemny (`public/css/style.css` + `public/js/theme.js`)

Motyw sterowany atrybutem `data-theme` na `<html>`, zapamiętywany w
`localStorage`, respektuje `prefers-color-scheme`. Skrypt anty-FOUC w `<head>`
ustawia motyw przed renderem.

```css
:root        { --primary: #14532d; --bg: #f4f8f5; --surface: #ffffff; /* ... */ }
[data-theme="dark"] { --primary: #2f9e6b; --bg: #0b1410; --surface: #10201a; /* ... */ }
```

### Responsywność – CSS media queries

```css
.equipment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.25rem;
}
@media (max-width: 768px) {
    .equipment-grid { grid-template-columns: 1fr; }
    .data-table { display: block; overflow-x: auto; }
}
@media (max-width: 480px) {
    .btn { width: 100%; }
}
```

---

## Scenariusz testowy

Krok po kroku – do ręcznej weryfikacji wszystkich wymagań:

1. **Uruchomienie:** `cp .env.example .env`, następnie `docker compose up -d --build`.
   Otwórz `http://localhost:8080` → strona główna (hero).
2. **Rejestracja:** wejdź w **Zarejestruj się**, podaj dane z hasłem krótszym niż
   8 znaków → lista błędów walidacji (422). Popraw dane → komunikat
   „Konto utworzone, możesz się zalogować".
3. **Logowanie – błędne dane:** zaloguj się złym hasłem → kod **401**, generyczny
   komunikat „E-mail lub hasło jest niepoprawne" (nie zdradza, czy konto istnieje).
4. **Rate limiting:** powtórz błędne logowanie 5 razy → kolejna próba zwraca
   **429** i komunikat o zbyt wielu próbach. Sprawdź audyt:
   `SELECT * FROM login_attempts;` (e-mail + IP, bez haseł).
5. **Logowanie klienta:** `klient@wpro.pl` / `klient123` → przekierowanie na
   stronę główną, w nagłówku imię i nazwisko (sesja).
6. **Wypożyczenie:** **Katalog** → wpisz „wiertarka" (lista odświeża się przez
   Fetch API, bez przeładowania) → **Szczegóły** → **Wypożycz teraz** → wybierz
   zakres dat w kalendarzu (daty przeszłe są zablokowane, koszt liczy się na
   żywo) → **Wypożycz** → wpis w „Moje wypożyczenia" ze statusem „Nowe".
   W bazie trigger zmniejszył `available_quantity` (sprawdź w katalogu).
7. **Zwrot:** w „Moje wypożyczenia" kliknij **Zwróć** → status „Zakończone",
   dostępność sprzętu wraca do pełnej.
8. **Kontrola uprawnień (403):** będąc zalogowanym jako klient, wejdź ręcznie na
   `http://localhost:8080/admin/users` → strona błędu **403** (RoleMiddleware).
9. **Brak sesji (302):** wyloguj się i wejdź na `/admin/users` → przekierowanie
   na `/login` (AuthMiddleware).
10. **CSRF (403):** wyślij `POST /login` bez tokenu (np. `curl -X POST -d
    "email=a@b.pl&password=x" http://localhost:8080/login`) → **403**.
11. **Panel pracownika:** zaloguj się jako `pracownik@wpro.pl` → zakładka
    **Wypożyczenia** (wszystkie wypożyczenia, oznaczanie zwrotów). Zakładki
    **Użytkownicy**/**Sprzęt** są niedostępne (403 przy wejściu ręcznym).
12. **Panel admina – CRUD:** zaloguj się jako `admin@wpro.pl` → **Sprzęt** →
    dodaj nowy sprzęt → edytuj go → wgraj zdjęcie → usuń. **Użytkownicy** →
    zmień rolę użytkownika → usuń konto testowe.
13. **Strony błędów:** `/equipment/999999` → **404**; `GET` na trasę POST
    (np. `/rentals`) → **405**.
14. **Widoki i funkcja bazy:**
    ```sql
    SELECT * FROM v_active_rentals;
    SELECT * FROM v_popular_equipment LIMIT 5;
    SELECT fn_calculate_rental_cost(1, 2, 3);  -- 25 zł × 2 szt. × 3 dni = 150.00
    ```
15. **Wyzwalacz:** w transakcji wstaw pozycję wypożyczenia i obserwuj spadek
    `available_quantity`; wycofaj (`ROLLBACK`) i sprawdź powrót wartości.
16. **Tryb ciemny / responsywność:** przełącz motyw ikoną w nagłówku (wybór
    przetrwa odświeżenie); zwęź okno < 768 px → układ jednokolumnowy.

---

## Uruchamianie testów

### Testy jednostkowe (PHPUnit) – 11 testów, 18 asercji

```bash
docker compose exec php composer install      # raz
docker compose exec php vendor/bin/phpunit --testdox
```

Pokryte testy:
- `UserModelTest` – mapowanie wierszy, role, pełne imię
- `AuthServiceTest` – walidacja danych, weryfikacja hasła (mock `UserRepository`)
- `LoginThrottleTest` – progi blokady, czyszczenie po sukcesie (mock repo)
- `CsrfTest` – stabilność i weryfikacja tokenu

### Testy integracyjne (Bash + curl)

```bash
bash tests/integration/smoke.sh
```

Sprawdzają m.in.:
- dostępność tras publicznych (`/`, `/login`, `/register`, `/equipment`) → 200
- nieistniejący zasób (`/equipment/999999`) → 404
- ochronę panelu admina bez logowania → 302 na `/login`
- odrzucenie `POST /login` bez tokenu CSRF → 403

---

## Checklista wymagań

### Funkcjonalność

- [x] Rejestracja użytkownika z walidacją
- [x] Logowanie / wylogowanie z sesją
- [x] Uprawnienia użytkowników (admin / pracownik / klient) weryfikowane w trakcie działania
- [x] Zarządzanie użytkownikami (zmiana roli, usuwanie)
- [x] Katalog sprzętu z wyszukiwaniem (Fetch API) i filtrem kategorii
- [x] Szczegóły sprzętu + podobny sprzęt
- [x] Wypożyczanie sprzętu z kalendarzem wyboru dat (walidacja dat po stronie serwera)
- [x] Historia „Moje wypożyczenia" + zgłoszenie zwrotu
- [x] Panel personelu: wszystkie wypożyczenia, oznaczanie zwrotów
- [x] Panel admina: CRUD sprzętu + wgrywanie zdjęć
- [x] Tryb jasny / ciemny
- [x] Responsywny design (CSS media queries)
- [x] Strony błędów: 400, 403, 404, 405, 500
- [x] Cały interfejs w języku polskim (z polskimi znakami)

### Baza danych

- [x] Relacja jeden-do-jednego: `users` → `user_profiles`
- [x] Relacja jeden-do-wielu: `categories` → `equipment`, `users` → `rentals`, `equipment` → `equipment_images`
- [x] Relacja wiele-do-wielu: `rentals` ↔ `equipment` (przez `rental_items`)
- [x] Minimum 2 widoki: `v_active_rentals`, `v_popular_equipment`
- [x] Minimum 1 wyzwalacz: `rental_items_after_insert`
- [x] Minimum 1 funkcja: `fn_calculate_rental_cost`
- [x] Transakcja na poziomie izolacji `SERIALIZABLE`
- [x] Klucze obce z akcjami `ON DELETE CASCADE / RESTRICT`
- [x] 3NF – brak redundancji i anomalii
- [x] Eksport bazy do plików SQL + dane przykładowe (seed)

### Kod i architektura

- [x] Architektura MVC + Service + Repository (własna, bez frameworka)
- [x] Zasady SOLID
- [x] OOP: klasy abstrakcyjne, enkapsulacja, dziedziczenie, wzorzec Singleton, readonly DTO
- [x] Composer + PSR-4 autoloading (+ własny autoloader fallback)
- [x] PHPUnit – testy jednostkowe (4 klasy testowe)
- [x] Testy integracyjne (Bash + curl)
- [x] Docker + docker-compose (Nginx + PHP-FPM + PostgreSQL)
- [x] Fetch API (wyszukiwarka, kalendarz dat)
- [x] Globalna obsługa błędów + dedykowane strony błędów
- [x] Security Bingo: 24/25 punktów
- [x] Dokumentacja: README, diagram ERD, diagram architektury, zrzuty ekranu, scenariusz testowy
- [x] Systematyczna historia commitów Git (rozwój od marca do czerwca)
