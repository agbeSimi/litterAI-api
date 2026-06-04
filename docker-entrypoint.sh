#!/bin/bash
set -e
php bin/console cache:clear --no-warmup
php bin/console cache:warmup
php bin/console assets:install public
php bin/console doctrine:migrations:migrate --no-interaction
exec apache2-foreground