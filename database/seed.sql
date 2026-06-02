-- Dane przykladowe - WypozyczalniaPRO
-- Hasla bcrypt do testow (password_hash, PASSWORD_BCRYPT):
--   admin@wpro.pl     / admin123
--   pracownik@wpro.pl / pracownik123
--   klient@wpro.pl    / klient123
--   klient2@wpro.pl   / klient123

INSERT INTO users (email, password_hash, first_name, last_name, role) VALUES
('admin@wpro.pl',     '$2y$10$zxt6zTiYXnk3jSGbX9G78ewBttwtrZn4jlUQyPtQd0oNrbCzAUV.2', 'Anna',  'Adminska',  'admin'),
('pracownik@wpro.pl', '$2y$10$9HeP83.LE0aBGVL6XFjDSuXtTMXtrTbt.cCx93ESCkm3Sd9K5OsqO', 'Piotr', 'Pracowski', 'pracownik'),
('klient@wpro.pl',    '$2y$10$mhbC/uhuCo4oMfVm1OtC2uPjMaIp9gxYuLZfXpLoFhg2gBWEyXB6u', 'Jan',   'Klientowicz','klient'),
('klient2@wpro.pl',   '$2y$10$mhbC/uhuCo4oMfVm1OtC2uPjMaIp9gxYuLZfXpLoFhg2gBWEyXB6u', 'Ewa',   'Wypozyczak','klient');

INSERT INTO user_profiles (user_id, phone, address) VALUES
(1, '+48 600 000 001', 'ul. Glowna 1, Krakow'),
(2, '+48 600 000 002', 'ul. Robocza 5, Krakow'),
(3, '+48 600 000 003', 'ul. Klienta 10, Krakow');

INSERT INTO categories (name, description) VALUES
('Narzedzia',     'Wiertarki, szlifierki, mloty udarowe'),
('Ogrod',         'Kosiarki, podkaszarki, pily lancuchowe'),
('Sport',         'Rowery, sprzet narciarski, namioty'),
('Elektronika',   'Projektory, drony, kamery sportowe');

INSERT INTO equipment (category_id, name, description, daily_rate, total_quantity, available_quantity) VALUES
(1, 'Wiertarka udarowa Bosch', 'Moc 800W, walizka, zestaw wiertel', 25.00, 3, 3),
(1, 'Mlot udarowy SDS',        'Do kucia i wiercenia w betonie',     45.00, 2, 2),
(2, 'Kosiarka spalinowa',      'Szerokosc koszenia 46cm',            60.00, 4, 4),
(2, 'Podkaszarka elektryczna', 'Lekka, do trawnikow',                20.00, 5, 5),
(3, 'Rower MTB 27.5',          'Amortyzator, 21 biegow',             35.00, 6, 6),
(3, 'Namiot 4-osobowy',        'Wodoodporny, latwy montaz',          30.00, 3, 3),
(4, 'Projektor FullHD',        'Jasnosc 3000 lumenow, HDMI',         80.00, 2, 2),
(4, 'Dron z kamera 4K',        'Czas lotu do 30 min, zasieg 5km',   120.00, 1, 1);

-- Przykladowe wypozyczenie (status zakonczony)
INSERT INTO rentals (user_id, status, start_date, end_date, total_cost) VALUES
(3, 'zakonczone', '2026-05-01', '2026-05-03', 75.00);
INSERT INTO rental_items (rental_id, equipment_id, quantity, daily_rate) VALUES
(1, 1, 1, 25.00);
-- Powyzszy INSERT zostanie obsluzony przez trigger - zmniejszy available;
-- nastepnie zwrot przywraca dostepnosc.
UPDATE equipment SET available_quantity = total_quantity WHERE id = 1;
