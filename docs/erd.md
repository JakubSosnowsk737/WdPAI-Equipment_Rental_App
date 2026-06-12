# Diagram ERD – WypożyczalniaPRO

Pełny ERD znajduje się w `erd.svg` (źródło edytowalne: `erd.drawio`).

Skrócona reprezentacja tekstowa:

```
users (1) ----- (1) user_profiles
   |
   | (1)
   |
   v (N)
rentals (1) ---- (N) rental_items (N) ---- (1) equipment (N) ---- (1) categories
                                                    |
                                                    | (1)
                                                    v (N)
                                              equipment_images
```

Klucze obce / akcje:

| Tabela docelowa | Klucz obcy             | ON UPDATE | ON DELETE |
|-----------------|------------------------|-----------|-----------|
| user_profiles   | user_id → users.id     | CASCADE   | CASCADE   |
| equipment       | category_id → categories.id | CASCADE | RESTRICT |
| equipment_images| equipment_id → equipment.id | CASCADE | CASCADE  |
| rentals         | user_id → users.id     | CASCADE   | RESTRICT  |
| rental_items    | rental_id → rentals.id | CASCADE   | CASCADE   |
| rental_items    | equipment_id → equipment.id | CASCADE | RESTRICT |

Dodatkowo tabela `login_attempts` (audyt prób logowania, rate limiting) –
samodzielna, bez kluczy obcych.
