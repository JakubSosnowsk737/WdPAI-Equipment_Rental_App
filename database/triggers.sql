-- Triggery - WypozyczalniaPRO

-- Trigger: automatyczne zmniejszanie dostepnej ilosci sprzetu
-- przy dodaniu pozycji do wypozyczenia. Eliminuje potrzebe
-- recznej aktualizacji equipment.available_quantity z poziomu PHP.

CREATE OR REPLACE FUNCTION trg_decrement_available()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE equipment
    SET available_quantity = available_quantity - NEW.quantity
    WHERE id = NEW.equipment_id;

    IF (SELECT available_quantity FROM equipment WHERE id = NEW.equipment_id) < 0 THEN
        RAISE EXCEPTION 'Brak dostepnych egzemplarzy sprzetu id=%', NEW.equipment_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS rental_items_after_insert ON rental_items;
CREATE TRIGGER rental_items_after_insert
AFTER INSERT ON rental_items
FOR EACH ROW
EXECUTE FUNCTION trg_decrement_available();
