-- Dane przykladowe - WypozyczalniaPRO
-- Hasla bcrypt do testow (password_hash, PASSWORD_BCRYPT):
--   admin@wpro.pl     / admin123
--   pracownik@wpro.pl / pracownik123
--   klient@wpro.pl    / klient123
--   klient2@wpro.pl   / klient123

INSERT INTO users (email, password_hash, first_name, last_name, role) VALUES
('admin@wpro.pl',     '$2y$10$zxt6zTiYXnk3jSGbX9G78ewBttwtrZn4jlUQyPtQd0oNrbCzAUV.2', 'Anna',  'Admińska',  'admin'),
('pracownik@wpro.pl', '$2y$10$9HeP83.LE0aBGVL6XFjDSuXtTMXtrTbt.cCx93ESCkm3Sd9K5OsqO', 'Piotr', 'Pracowski', 'pracownik'),
('klient@wpro.pl',    '$2y$10$mhbC/uhuCo4oMfVm1OtC2uPjMaIp9gxYuLZfXpLoFhg2gBWEyXB6u', 'Jan',   'Klientowicz','klient'),
('klient2@wpro.pl',   '$2y$10$mhbC/uhuCo4oMfVm1OtC2uPjMaIp9gxYuLZfXpLoFhg2gBWEyXB6u', 'Ewa',   'Wypożyczak','klient');

INSERT INTO user_profiles (user_id, phone, address) VALUES
(1, '+48 600 000 001', 'ul. Główna 1, Kraków'),
(2, '+48 600 000 002', 'ul. Robocza 5, Kraków'),
(3, '+48 600 000 003', 'ul. Klienta 10, Kraków');

INSERT INTO categories (name, description) VALUES
('Narzędzia',     'Wiertarki, szlifierki, młoty udarowe'),
('Ogród',         'Kosiarki, podkaszarki, piły łańcuchowe'),
('Sport',         'Rowery, sprzęt narciarski, namioty'),
('Elektronika',   'Projektory, drony, kamery sportowe');

INSERT INTO equipment (category_id, name, description, daily_rate, total_quantity, available_quantity) VALUES
(1, 'Wiertarka udarowa Bosch', 'Moc 800W, walizka, zestaw wierteł',  25.00, 3, 3),
(1, 'Młot udarowy SDS',        'Do kucia i wiercenia w betonie',     45.00, 2, 2),
(2, 'Kosiarka spalinowa',      'Szerokość koszenia 46 cm',           60.00, 4, 4),
(2, 'Podkaszarka elektryczna', 'Lekka, do trawników',                20.00, 5, 5),
(3, 'Rower MTB 27.5',          'Amortyzator, 21 biegów',             35.00, 6, 6),
(3, 'Namiot 4-osobowy',        'Wodoodporny, łatwy montaż',          30.00, 3, 3),
(4, 'Projektor FullHD',        'Jasność 3000 lumenów, HDMI',         80.00, 2, 2),
(4, 'Dron z kamerą 4K',        'Czas lotu do 30 min, zasięg 5 km',  120.00, 1, 1);

-- Przykladowe wypozyczenie (status zakonczony)
INSERT INTO rentals (user_id, status, start_date, end_date, total_cost) VALUES
(3, 'zakonczone', '2026-05-01', '2026-05-03', 75.00);
INSERT INTO rental_items (rental_id, equipment_id, quantity, daily_rate) VALUES
(1, 1, 1, 25.00);
-- Powyzszy INSERT zostanie obsluzony przez trigger - zmniejszy available;
-- nastepnie zwrot przywraca dostepnosc.
UPDATE equipment SET available_quantity = total_quantity WHERE id = 1;
