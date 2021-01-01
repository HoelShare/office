pecl install pcov
docker-php-ext-enable pcov
APP_ENV=test bin/console doctrine:database:create || true
APP_ENV=test bin/console doctrine:migrations:migrate -n --all-or-nothing true || true
