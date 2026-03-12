#!/bin/bash

printf "CI_ENVIRONMENT = production\napp.baseURL = https://dbsubasta-production.up.railway.app/\ndatabase.default.hostname = switchyard.proxy.rlwy.net\ndatabase.default.database = railway\ndatabase.default.username = root\ndatabase.default.password = kphYyKHHiTBjQDacodwZONPuwpZipLPs\ndatabase.default.port = 43411\ndatabase.default.DBDriver = MySQLi\n" > /var/www/html/.env

echo "Starting on port: $PORT"
php spark serve --host 0.0.0.0 --port ${PORT:-8080}
```

Y en Railway → db_subasta → **Settings → Start Command** asegúrate que dice exactamente:
```
/start.sh