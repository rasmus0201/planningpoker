# Backend for Planning Poker
Build with AdonisJS.

## Prerequisites
- Nginx
- Certbot
- Node >= v16.19.0
- npm >= 8.19.3
- pm2 >= 5.2.2

## Installation & setup for production
1. `git clone git@github.com:rasmus0201/planningpoker.git`
2. `cd planningpoker/backend`
3. Create the `.env` file with DB credentials and other config. `cp .env.example .env`
4. `ENV_PATH={$FULL_PATH_TO_PLANNING_POKER}/backend/.env node ace migration:fresh`
5. Edit `ecosystem.config.js` to correspond with the correct env path.
6. `pm2 start && pm2 save`
7. Setup the nginx config for the site using the blueprint file `nginx-site.conf` in the root of the config. The site also needs a TLS-cert, so use certbot for this.

## Development setup
1. `git clone git@github.com:rasmus0201/planningpoker.git`
2. `cd planningpoker/backend`
3. `npm i`
4. `node ace serve --watch`
5. Optional run the scheduler using: `node ace scheduler`
6. When done remember to commit production assets - `node ace build --production --ignore-ts-errors
 && cd build && npm ci --production`
