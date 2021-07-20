#!/bin/sh

#rm /var/www/planningpoker.rasmusbundsgaard.dk/db.sqlite
# How to 'clear'/reset database?
php /var/www/planningpoker.rasmusbundsgaard.dk/bootstrap/migrations.php
supervisorctl restart planningpoker:*
systemctl restart nginx
