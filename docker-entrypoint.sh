#!/bin/bash
set -e
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod
php bin/console assets:install public --env=prod
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
exec apache2-foreground