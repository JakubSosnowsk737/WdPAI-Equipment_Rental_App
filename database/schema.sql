-- Schemat bazy danych - WypozyczalniaPRO
-- Wersja: 0.2 (tabela users)

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

-- TODO:
-- - categories, equipment
-- - rentals + rental_items
-- - 1:1 user_profiles (telefon, adres)
-- - widoki, trigger, funkcja
