# Diagram ERD - WypozyczalniaPRO

Pelny ERD znajduje sie w `erd.png` (zrodlo: `erd.drawio`).

Skrocona reprezentacja tekstowa:

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
| user_profiles   | user_id -> users.id    | CASCADE   | CASCADE   |
| equipment       | category_id -> categories.id | CASCADE | RESTRICT |
| equipment_images| equipment_id -> equipment.id | CASCADE | CASCADE  |
| rentals         | user_id -> users.id    | CASCADE   | RESTRICT  |
| rental_items    | rental_id -> rentals.id| CASCADE   | CASCADE   |
| rental_items    | equipment_id -> equipment.id | CASCADE | RESTRICT |
