#!/bin/sh

php /var/www/planningpoker.rasmusbundsgaard.dk/bootstrap/migrations.php
supervisorctl restart planningpoker:*
systemctl restart nginx
