#!/bin/bash
set -e
php bin/console cache:clear --no-warmup
php bin/console cache:warmup
php bin/console assets:install public
php bin/console doctrine:schema:update --force
exec apache2-foreground