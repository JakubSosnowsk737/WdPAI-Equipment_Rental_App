-- Schemat bazy danych - WypozyczalniaPRO
-- Wersja: 0.3 (kategorie + sprzet)

DROP TABLE IF EXISTS equipment CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;

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
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- TODO:
-- - rentals + rental_items (M:N z atrybutem)
-- - user_profiles (1:1)
-- - equipment_images (1:N)
-- - widoki, trigger, funkcja
