#!/bin/sh

#rm /var/www/planningpoker.rasmusbundsgaard.dk/db.sqlite
# How to 'clear'/reset database?
supervisorctl restart planningpoker:*
systemctl restart nginx
