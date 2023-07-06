# Backend for Planning Poker
Build with Laravel.

## Prerequisites
- Nginx
- Certbot
- Composer
- PHP >= 8.2
- Pusher account (or soketi setup)

## Installation & setup for production
1. `git clone git@github.com:rasmus0201/planningpoker.git`
2. `cd planningpoker/backend && composer install`
3. Create the `.env` file with DB credentials and other config. `cp .env.example .env`
4. Edit `.env` to match correct variables on your system. Also set APP_ENV to `production`.
5. `php artisan migrate:fresh`
6. Setup the nginx config for the site using the blueprint file `nginx-site.conf` in the root of the config. The site also needs a TLS-cert, so use certbot for this.

## Development setup
1. `git clone git@github.com:rasmus0201/planningpoker.git`
2. `cd planningpoker/backend`
3. `composer install`
4. `php artisan serve`
