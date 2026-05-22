-- Widoki bazy danych - WypozyczalniaPRO

-- Widok 1: Aktywne wypozyczenia z danymi klienta i sprzetu
DROP VIEW IF EXISTS v_active_rentals;
CREATE VIEW v_active_rentals AS
SELECT
    r.id                                AS rental_id,
    u.id                                AS user_id,
    u.first_name || ' ' || u.last_name  AS customer,
    u.email                             AS customer_email,
    e.name                              AS equipment_name,
    c.name                              AS category,
    ri.quantity                         AS quantity,
    r.start_date,
    r.end_date,
    r.total_cost,
    r.status
FROM rentals r
JOIN users u           ON u.id = r.user_id
JOIN rental_items ri   ON ri.rental_id = r.id
JOIN equipment e       ON e.id = ri.equipment_id
JOIN categories c      ON c.id = e.category_id
WHERE r.status IN ('nowe','aktywne');

-- Widok 2: Ranking popularnosci sprzetu (liczba wypozyczen)
DROP VIEW IF EXISTS v_popular_equipment;
CREATE VIEW v_popular_equipment AS
SELECT
    e.id                                AS equipment_id,
    e.name                              AS equipment_name,
    c.name                              AS category,
    COUNT(ri.rental_id)                 AS rentals_count,
    COALESCE(SUM(ri.quantity), 0)       AS units_rented
FROM equipment e
JOIN categories c       ON c.id = e.category_id
LEFT JOIN rental_items ri ON ri.equipment_id = e.id
GROUP BY e.id, e.name, c.name
ORDER BY rentals_count DESC;
