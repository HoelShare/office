pecl install pcov
docker-php-ext-enable pcov
APP_ENV=test bin/console doctrine:database:create
APP_ENV=test bin/console doctrine:migrations:migrate -n
