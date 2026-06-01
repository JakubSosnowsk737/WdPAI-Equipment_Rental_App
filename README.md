# WypozyczalniaPRO

Aplikacja webowa do obslugi wypozyczalni sprzetu (narzedzia, ogrod, sport,
elektronika). Projekt realizowany w ramach przedmiotu WdPAI.

## Stack technologiczny

- **PHP 8.2** obiektowo, bez frameworka, autoloader PSR-4 wlasny
- **PostgreSQL 15** (widoki, trigger, funkcja PL/pgSQL, transakcje SERIALIZABLE)
- **HTML5 + CSS** (media queries, brak gotowych szablonow)
- **JavaScript** + **FETCH API** (dynamiczne wyszukiwanie w katalogu)
- **Docker / docker-compose** (PHP-FPM, PostgreSQL, nginx)
- **PHPUnit** + bashowy smoke test

## Architektura

Klasyczny MVC z dodatkowa warstwa **Service** (logika biznesowa) i **Repository**
(dostep do bazy). Middleware obsluguje uwierzytelnienie i autoryzacje.

```
[nginx] -> public/index.php (front controller)
         -> Router -> [Middleware] -> Controller
                                       |-> Service -> Repository -> PDO -> Postgres
                                       |-> View (renderowane szablony PHP)
```

Zasady SOLID:
- SRP: kontrolery cienkie, logika w Service, dostep do bazy w Repository
- OCP: bazowe klasy `AbstractController`, `AbstractRepository`
- LSP/DIP: serwisy przyjmuja repozytoria w konstruktorze (mockowalne w testach)
- ISP: male, dedykowane klasy (Session, Request, Response, View)

## Uruchomienie

```bash
cp .env.example .env
docker-compose up -d --build
```

Aplikacja dostepna pod `http://localhost:8080`.

Baza danych jest inicjalizowana z plikow w `database/` automatycznie przez
Postgresa (volume `docker-entrypoint-initdb.d`). Reczna instalacja:

```bash
psql -U app -d wypozyczalnia -f database/install.sql
```

## Konta testowe

| Email                | Haslo         | Rola      |
|----------------------|---------------|-----------|
| admin@wpro.pl        | admin123      | admin     |
| pracownik@wpro.pl    | pracownik123  | pracownik |
| klient@wpro.pl       | klient123     | klient    |

## Baza danych

### Tabele i relacje

| Tabela            | Typ relacji                   |
|-------------------|-------------------------------|
| `users`           | -                             |
| `user_profiles`   | 1:1 z `users`                 |
| `categories`      | -                             |
| `equipment`       | 1:N z `categories`            |
| `equipment_images`| 1:N z `equipment`             |
| `rentals`         | 1:N z `users`                 |
| `rental_items`    | M:N (`rentals` <-> `equipment`) z atrybutami `quantity`, `daily_rate` |

ERD: `docs/erd.png` (zrodlo edytowalne: `docs/erd.drawio`).

### Widoki, trigger, funkcja

- `v_active_rentals` - aktywne wypozyczenia z JOIN po 4 tabelach
- `v_popular_equipment` - ranking popularnosci sprzetu (JOIN + GROUP BY)
- trigger `rental_items_after_insert` -> `trg_decrement_available()`
  automatycznie zmniejsza `equipment.available_quantity` po dodaniu pozycji
- funkcja `fn_calculate_rental_cost(equipment_id, quantity, days)` zwraca koszt
- tworzenie wypozyczenia: `SET TRANSACTION ISOLATION LEVEL SERIALIZABLE`
- akcje FK: `ON UPDATE CASCADE` + `ON DELETE RESTRICT/CASCADE` (zalezne od relacji)

Baza jest w 3 postaci normalnej - kazda kolumna zalezy od PK (cena dzienna jest
kopiowana do `rental_items.daily_rate`, bo dotyczy momentu wypozyczenia,
a nie aktualnej ceny, wiec nie jest redundancja).

## Funkcjonalnosci

- Rejestracja / logowanie / wylogowanie (sesja, regeneracja id, samesite=Lax)
- Role: **admin**, **pracownik**, **klient** (RoleMiddleware)
- Klient: katalog, szczegoly, wypozyczenie, zwrot, historia "Moje wypozyczenia"
- Pracownik: lista wszystkich wypozyczen, oznaczenie zwrotu
- Admin: zarzadzanie uzytkownikami (zmiana roli, usuwanie), CRUD sprzetu,
  upload obrazkow
- Dynamiczne wyszukiwanie sprzetu przez FETCH API (`/api/equipment`)
- Strony bledow 400/403/404/500 obslugiwane globalnie (`ErrorHandler`)

## Scenariusz testowy

1. `docker-compose up -d --build`
2. Otworz `http://localhost:8080` -> strona glowna
3. Zaloguj sie jako klient `klient@wpro.pl` / `klient123`
4. Przejdz do **Katalog**, wpisz "wiertarka" - lista odswieza sie przez fetch
5. Kliknij szczegoly -> **Wypozycz**, wybierz daty, zatwierdz
6. **Moje wypozyczenia** -> sprawdz wpis -> kliknij **Zwroc**
7. Wyloguj i zaloguj jako `admin@wpro.pl` / `admin123`
8. **Uzytkownicy** - zmien role wybranemu uzytkownikowi
9. **Sprzet** -> Dodaj nowy sprzet -> dodaj obrazek
10. Otworz w incognito `http://localhost:8080/admin/users` bez logowania -> 302
    na `/login` (AuthMiddleware) lub 403 dla zalogowanego klienta (RoleMiddleware)
11. Sprawdz widoki bazy:
    ```sql
    SELECT * FROM v_active_rentals;
    SELECT * FROM v_popular_equipment LIMIT 5;
    SELECT fn_calculate_rental_cost(1, 2, 3);
    ```

## Testy

- **Jednostkowe** (PHPUnit):
  ```bash
  vendor/bin/phpunit
  ```
  Pokrywaja `User` (model) i `AuthService` (mock UserRepository).
- **Integracyjne** (curl):
  ```bash
  bash tests/integration/smoke.sh
  ```

## Checklista wymagan

- [x] Docker + docker-compose (PHP + Postgres + nginx)
- [x] GIT z historia rozwoju (50 commitow)
- [x] HTML5, CSS, JavaScript (FETCH API)
- [x] PHP obiektowy, bez frameworka
- [x] PostgreSQL
- [x] MVC + Service + Repository + Middleware
- [x] Estetyczny, responsywny UI (media queries 768/480)
- [x] Logowanie, sesja, wylogowanie
- [x] Role i weryfikacja uprawnien w trakcie dzialania
- [x] Zarzadzanie uzytkownikami
- [x] Relacje 1:1, 1:N, M:N
- [x] 2 widoki SQL z JOIN po wielu tabelach
- [x] Trigger PL/pgSQL
- [x] Funkcja PL/pgSQL
- [x] Transakcja SERIALIZABLE przy tworzeniu wypozyczenia
- [x] FK z akcjami CASCADE/RESTRICT
- [x] 3 postacie normalne, brak redundancji
- [x] Eksport bazy do plikow SQL
- [x] README z opisem, ERD, screenami, instrukcja, scenariuszem
- [x] PHPUnit (2 zestawy testow)
- [x] Bashowy smoke test endpointow
- [x] Strony bledow 400/403/404/500
- [x] SOLID + brak duplikacji kodu

## Struktura katalogow

```
.
├── database/        # schema.sql, views.sql, triggers.sql, functions.sql, seed.sql
├── docker/          # Dockerfile PHP, konfiguracja nginx
├── docs/            # ERD, screeny, diagram architektury
├── public/          # front controller, CSS, JS, uploads
├── src/
│   ├── Controllers/ # AbstractController + Home/Auth/User/Equipment/Rental
│   ├── Core/        # Autoloader, Config, Database, Router, Request, Response,
│   │                # View, Session, ErrorHandler
│   ├── Middleware/  # AuthMiddleware, RoleMiddleware
│   ├── Models/      # User, Category, Equipment, Rental
│   ├── Repositories/# AbstractRepository + UserRepo, EquipmentRepo, ...
│   └── Services/    # AuthService, RentalService
├── tests/
│   ├── Unit/        # UserModelTest, AuthServiceTest
│   └── integration/ # smoke.sh
├── views/           # layout.php + szablony PHP
├── docker-compose.yml
├── .env.example
└── phpunit.xml
```

## Diagram warstw

```
+--------------------+
|  Widoki (PHP/HTML) |  <- prezentacja
+---------+----------+
          |
+---------v----------+
|   Kontrolery       |  <- przyjecie zadania, walidacja prosta
+---------+----------+
          |
+---------v----------+
|     Services       |  <- logika biznesowa, transakcje
+---------+----------+
          |
+---------v----------+
|   Repozytoria      |  <- SQL, PDO
+---------+----------+
          |
+---------v----------+
|   PostgreSQL       |  <- widoki, trigger, funkcja
+--------------------+
```

## Screeny

W katalogu `docs/screenshots/`:
- `home.png`, `catalog.png`, `equipment-detail.png`
- `mobile-home.png`, `mobile-catalog.png` (wersja mobilna)
- `admin-users.png`, `admin-equipment.png`, `admin-rentals.png`
