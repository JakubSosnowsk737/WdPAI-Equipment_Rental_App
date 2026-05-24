-- Funkcje SQL - WypozyczalniaPRO

-- Funkcja oblicza koszt wypozyczenia dla danego sprzetu, ilosci i liczby dni.
-- Zwraca NUMERIC(10,2). Zaokraglenie do 2 miejsc.
CREATE OR REPLACE FUNCTION fn_calculate_rental_cost(
    p_equipment_id INTEGER,
    p_quantity     INTEGER,
    p_days         INTEGER
) RETURNS NUMERIC(10,2) AS $$
DECLARE
    v_rate NUMERIC(8,2);
BEGIN
    SELECT daily_rate INTO v_rate FROM equipment WHERE id = p_equipment_id;
    IF v_rate IS NULL THEN
        RAISE EXCEPTION 'Sprzet id=% nie istnieje', p_equipment_id;
    END IF;
    IF p_quantity < 1 OR p_days < 1 THEN
        RAISE EXCEPTION 'Niepoprawne parametry: quantity=%, days=%', p_quantity, p_days;
    END IF;
    RETURN ROUND(v_rate * p_quantity * p_days, 2);
END;
$$ LANGUAGE plpgsql;
