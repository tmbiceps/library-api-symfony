# Library API (Symfony) – Authors / Books / Copies

Projekt spełnia wymagania testów: REST CRUD dla encji **Author**, **Book**, **Copy**.

## Wymagania
- PHP 8.2+
- Composer

## Instalacja
```bash
composer install
```

## Baza danych (SQLite)
Projekt domyślnie używa SQLite: `var/database.db`.

Uruchom migracje:
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

## Uruchomienie serwera
Najprościej na wbudowanym serwerze PHP:
```bash
php -S 127.0.0.1:8000 -t public
```

API będzie dostępne pod:
- `http://127.0.0.1:8000/authors`
- `http://127.0.0.1:8000/books`
- `http://127.0.0.1:8000/copies`

## Weryfikacja testami z pliku HTML/JS
W repo dodałem dostarczone testy do `public/tests/`.

1. Upewnij się, że serwer działa (komenda wyżej).
2. Otwórz w przeglądarce:
   - `http://127.0.0.1:8000/tests/index.html`
3. Testy powinny automatycznie wykonać requesty do API i pokazać wynik.

> Jeśli Twoje testy mają w JS stały adres API (np. `http://localhost:8000`), uruchamiaj na tym samym porcie/hostcie.

## Kontrakt API (najważniejsze)
- `POST /authors` body: `{ "first_name": "Jan", "last_name": "Kowalski" }` → `201` + obiekt autora
- `PUT /authors/{id}` body: `{ "id": 1, "first_name": "Jan", "last_name": "Nowak" }` → `204`
- `POST /books` body: `{ "title": "Tytuł", "year": 2020, "authorId": 1 }` → `201` + obiekt książki z polem `author` (pełny autor)
- `GET /books?authorId=1` → tylko książki danego autora
