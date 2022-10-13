#!/bin/sh

php7.4 /var/www/planningpoker.rasmusbundsgaard.dk/bootstrap/migrations.php
supervisorctl restart planningpoker:*
systemctl restart nginx
