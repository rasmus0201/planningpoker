#!/bin/sh

rm /var/www/planningpoker.rasmusbundsgaard.dk/db.sqlite
supervisorctl restart planningpoker:*
systemctl restart nginx

