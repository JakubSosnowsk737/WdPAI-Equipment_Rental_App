-- Schemat bazy danych - WypozyczalniaPRO
-- Wersja: 0.4 (relacje 1:1, 1:N, M:N)

DROP TABLE IF EXISTS rental_items   CASCADE;
DROP TABLE IF EXISTS rentals        CASCADE;
DROP TABLE IF EXISTS equipment_images CASCADE;
DROP TABLE IF EXISTS equipment      CASCADE;
DROP TABLE IF EXISTS categories     CASCADE;
DROP TABLE IF EXISTS user_profiles  CASCADE;
DROP TABLE IF EXISTS users          CASCADE;

CREATE TABLE users (
    id              SERIAL PRIMARY KEY,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    first_name      VARCHAR(80)  NOT NULL,
    last_name       VARCHAR(80)  NOT NULL,
    role            VARCHAR(20)  NOT NULL DEFAULT 'klient'
                    CHECK (role IN ('admin','pracownik','klient')),
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- relacja 1:1 (uzytkownik -> profil) - klucz glowny jest jednoczesnie kluczem obcym
CREATE TABLE user_profiles (
    user_id     INTEGER PRIMARY KEY,
    phone       VARCHAR(20),
    address     VARCHAR(200),
    CONSTRAINT fk_profile_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE categories (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- relacja 1:N (categories -> equipment)
CREATE TABLE equipment (
    id                  SERIAL PRIMARY KEY,
    category_id         INTEGER NOT NULL,
    name                VARCHAR(150) NOT NULL,
    description         TEXT,
    daily_rate          NUMERIC(8,2) NOT NULL CHECK (daily_rate >= 0),
    total_quantity      INTEGER NOT NULL DEFAULT 1 CHECK (total_quantity >= 0),
    available_quantity  INTEGER NOT NULL DEFAULT 1 CHECK (available_quantity >= 0),
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipment_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_available_le_total CHECK (available_quantity <= total_quantity)
);

-- relacja 1:N (equipment -> equipment_images)
CREATE TABLE equipment_images (
    id              SERIAL PRIMARY KEY,
    equipment_id    INTEGER NOT NULL,
    image_path      VARCHAR(255) NOT NULL,
    CONSTRAINT fk_image_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- naglowek wypozyczenia (relacja 1:N users -> rentals)
CREATE TABLE rentals (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL,
    status      VARCHAR(20) NOT NULL DEFAULT 'nowe'
                CHECK (status IN ('nowe','aktywne','zakonczone','anulowane')),
    start_date  DATE NOT NULL,
    end_date    DATE NOT NULL,
    total_cost  NUMERIC(10,2) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rental_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_dates CHECK (end_date >= start_date)
);

-- relacja M:N (rentals <-> equipment) z atrybutem dodatkowym
CREATE TABLE rental_items (
    rental_id       INTEGER NOT NULL,
    equipment_id    INTEGER NOT NULL,
    quantity        INTEGER NOT NULL CHECK (quantity > 0),
    daily_rate      NUMERIC(8,2) NOT NULL,
    PRIMARY KEY (rental_id, equipment_id),
    CONSTRAINT fk_item_rental
        FOREIGN KEY (rental_id) REFERENCES rentals(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_item_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- TODO (nastepne iteracje):
-- - widoki podsumowujace
-- - trigger zmieniajacy available_quantity
-- - funkcja liczaca koszt wypozyczenia
