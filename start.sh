#!/bin/bash
# Iniciar php-fpm en background
php-fpm -D
# Esperar que php-fpm esté listo
sleep 2
# Iniciar nginx en foreground
nginx -g 'daemon off;'