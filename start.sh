#!/bin/bash

# Escribir variables al .env de CI4
printf "CI_ENVIRONMENT = production\n\
app.baseURL = ${APP_BASE_URL}\n\
database.default.hostname = ${DB_HOSTNAME}\n\
database.default.database = ${DB_DATABASE}\n\
database.default.username = ${DB_USERNAME}\n\
database.default.password = ${DB_PASSWORD}\n\
database.default.port = ${DB_PORT}\n\
database.default.DBDriver = MySQLi\n\
JWT_SECRET_KEY = ${JWT_SECRET_KEY}\n\
JWT_TTL = ${JWT_TTL}\n\
CLOUDINARY_CLOUD_NAME = ${CLOUDINARY_CLOUD_NAME}\n\
CLOUDINARY_API_KEY = ${CLOUDINARY_API_KEY}\n\
CLOUDINARY_API_SECRET = ${CLOUDINARY_API_SECRET}\n" > /var/www/html/.env

echo "FCM_CREDENTIALS = ${FCM_CREDENTIALS}" >> /var/www/html/.env
echo "ADMIN_KEY = ${ADMIN_KEY}" >> /var/www/html/.env
echo "GOOGLE_CLIENT_ID = ${GOOGLE_CLIENT_ID}" >> /var/www/html/.env
echo "FACEBOOK_APP_ID = ${FACEBOOK_APP_ID}" >> /var/www/html/.env
echo "FACEBOOK_APP_SECRET = ${FACEBOOK_APP_SECRET}" >> /var/www/html/.env

# Correr migraciones si se pasa el argumento
if [ "$1" = "migrate" ]; then
    php spark migrate --all
fi

php spark serve --host 0.0.0.0 --port $PORT