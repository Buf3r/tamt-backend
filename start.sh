#!/bin/bash
printf "CI_ENVIRONMENT = production\napp.baseURL = https://dbsubasta-production.up.railway.app/\ndatabase.default.hostname = switchyard.proxy.rlwy.net\ndatabase.default.database = railway\ndatabase.default.username = root\ndatabase.default.password = kphYyKHHiTBjQDacodwZONPuwpZipLPs\ndatabase.default.port = 43411\ndatabase.default.DBDriver = MySQLi\nJWT_SECRET_KEY = ${JWT_SECRET_KEY}\nJWT_TTL = ${JWT_TTL}\nCLOUDINARY_CLOUD_NAME = ${CLOUDINARY_CLOUD_NAME}\nCLOUDINARY_API_KEY = ${CLOUDINARY_API_KEY}\nCLOUDINARY_API_SECRET = ${CLOUDINARY_API_SECRET}\nFCM_CREDENTIALS = ${FCM_CREDENTIALS}\n" > /var/www/html/.env

# Correr migraciones si se pasa el argumento
if [ "$1" = "migrate" ]; then
    php spark migrate --all
fi

php spark serve --host 0.0.0.0 --port $PORT