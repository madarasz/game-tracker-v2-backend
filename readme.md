# Installation

`cp .env.example .env`

Edit .env configuration. Set JWT_SECRET.

```
php artisan key:generate
php artisan migrate
php artisan db:seed --class=TestSeeder
```

# Running in dev

`php -S localhost:8000 -t ./public`
