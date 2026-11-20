#!/bin/bash
set -e

PORT="${PORT:-10000}"

# Escribir variables al .env de CI4
cat > /var/www/html/.env <<ENVEOF
CI_ENVIRONMENT = production
app.baseURL = ${APP_BASE_URL}
database.default.hostname = ${DB_HOSTNAME}
database.default.database = ${DB_DATABASE}
database.default.username = ${DB_USERNAME}
database.default.password = ${DB_PASSWORD}
database.default.port = ${DB_PORT:-3306}
database.default.DBDriver = MySQLi
JWT_SECRET_KEY = ${JWT_SECRET_KEY}
JWT_TTL = ${JWT_TTL:-60}
CLOUDINARY_CLOUD_NAME = ${CLOUDINARY_CLOUD_NAME}
CLOUDINARY_API_KEY = ${CLOUDINARY_API_KEY}
CLOUDINARY_API_SECRET = ${CLOUDINARY_API_SECRET}
ADMIN_KEY = ${ADMIN_KEY}
GOOGLE_CLIENT_ID = ${GOOGLE_CLIENT_ID}
FACEBOOK_APP_ID = ${FACEBOOK_APP_ID}
FACEBOOK_APP_SECRET = ${FACEBOOK_APP_SECRET}
ENVEOF

# El JSON de FCM va aparte (contiene comillas)
echo "FCM_CREDENTIALS = ${FCM_CREDENTIALS}" >> /var/www/html/.env

cd /var/www/html

# Migraciones opcionales: define RUN_MIGRATIONS=true en Render (solo la primera vez)
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    php spark migrate --all
fi

# Servidor de PHP con varios workers para atender peticiones en paralelo
export PHP_CLI_SERVER_WORKERS="${PHP_CLI_SERVER_WORKERS:-4}"
exec php spark serve --host 0.0.0.0 --port "$PORT"
