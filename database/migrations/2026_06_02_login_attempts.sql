-- Migracja: audyt prob logowania (dla istniejacych baz).
-- Dla swiezych instalacji tabela jest juz w schema.sql.

CREATE TABLE IF NOT EXISTS login_attempts (
    id          SERIAL PRIMARY KEY,
    email       VARCHAR(150) NOT NULL,
    ip_address  VARCHAR(45)  NOT NULL,
    successful  BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_login_attempts_email_time ON login_attempts (email, created_at);
CREATE INDEX IF NOT EXISTS idx_login_attempts_ip_time    ON login_attempts (ip_address, created_at);
