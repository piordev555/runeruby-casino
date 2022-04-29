#!/bin/bash

php artisan queue:clear
php artisan game:chain all

nohup laravel-echo-server start & disown
nohup php artisan win5x:subscribe & disown
nohup php artisan queue:listen --sleep 0.01 & disown

nohup node bullvsbear.js & disown
nohup cd BitGoJS/modules/express && npm run start & disown
