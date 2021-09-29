# Planning Poker

Used for estimating stories in agile software development.

It is build upon a PHP application utilizing WebSocket
from [Ratchet](https://github.com/ratchetphp/Ratchet) and parts from the [illuminate ecosystem](https://github.com/illuminate).

# Features

- Support for different "games" - e.g. different teams.
- Support for player users.
- Support for guest users (they will be spectators in the game).
- Support for gamemaster user (will control the game).

### Player UI:
- Select card and vote.
- See an overview of cards from players, when round finished.

### Admin UI:
- See connected users and if the voted or not.
- Start the game.
- Stop the game.
- Start next round of playing.
- Force showoff. If you don't wan't to wait for all players to finish voting.
- Overview of players' votes when round finished.

### Settings (floating buttons to the top right):
- Diamond: Toggle animated/static background.
- Trash: Remove stored state (logs out).
- Speaker: Toggle sound effects.

# How to install and setup


### Cloning
Use by cloning, composer install.
```bash
git clone git@github.com:rasmus0201/planningpoker.git
composer install
```


### Database - MySQL
Copy `.env.example` to `.env` and update the variables to use actual database settings.

Then run the migration tool: `php /var/www/${DOMAIN}/bootstrap/migrations.php`.


### Nginx and websocket reverse proxy
Next up running on nginx you need to setup a virtual host for both the website and the websocket. Replace `${DOMAIN}` with the actual domain name and TLD of you website (So fx. `scrumplanningpoker.com`). Note that the websocket is going to run on port `9000`, so you need to make sure that's available.

This assumes you are using [letsencrypt](https://letsencrypt.org/) and probably you want to use [certbot](https://certbot.eff.org/) for managning certicates, and have certificates installed for the domain.

```nginx
upstream planningpoker_websocket {
    server ${DOMAIN}:9000;
}

server {
    listen [::]:443 ssl;
    listen 443 ssl;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN}/privkey.pem;

    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    root /var/www/${DOMAIN}/public;
    server_name ${DOMAIN};
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        try_files $fastcgi_script_name =404;
        set $path_info $fastcgi_path_info;
        fastcgi_param PATH_INFO $path_info;
        fastcgi_index index.php;

        # Assuming fastcgi is installed and located in /etc/nginx
        include fastcgi.conf;

        # Assuming use of PHP 7.4
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    location /websocket {
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_pass_request_headers on;
        proxy_redirect off;
        proxy_connect_timeout 1h;
        proxy_send_timeout 1h;
        proxy_read_timeout 1h;

        proxy_pass http://planningpoker_websocket;
    }
}

# Only run on HTTPS, so redirect :80 -> :442
server {
    listen 80;
    listen [::]:80;

    server_name ${DOMAIN};

    return 301 https://$host$request_uri;
}
```

Make sure the config is in `/etc/nginx/sites-available/${DOMAIN}.conf` and a symlink in `/etc/nginx/sites-enabled/${DOMAIN}.conf`. Then restart nginx: `sudo systemctl reload nginx`.



### Supervisor - for managing and running the websocket app.
First you need to [install Supervisor](http://supervisord.org/installing.html) if you haven't already.

Next, make a file named `planningpoker-worker.conf` in `/etc/supervisor/conf.d`  containing the following:

```bash
[program:planningpoker]
command                 = php /var/www/${DOMAIN}/app.php
process_name            = %(program_name)s_%(process_num)02d
numprocs                = 1
autostart               = true
autorestart             = true
user                    = www-data
redirect_stderr         = true
stdout_logfile          = /var/log/supervisor/planningpoker-worker.log
```

and then start supervisor worker:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start planningpoker
```


### Cron - for resetting the planning poker database.
Currently there is no way of restoring the planning poker games to are pristine state again. So this is done nightly by a cronjob. To start editing crontab type `crontab -e`. Then insert the following line:

```bash
0 3 * * * /bin/bash /var/www/${DOMAIN}/cron.sh
```

# Contributing
PRs and issues are welcomed and will be adresssed as soon as possible, but don't expect immediate replies and fixes :sweat_smile:

# Credits:
 - [Redbooth Planning poker card faces](https://github.com/redbooth/scrum-poker-cards) - [License](https://github.com/redbooth/scrum-poker-cards/blob/master/LICENSE)
