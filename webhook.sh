composer install
yarn install --suppress-optional fsevents
php bin/console a:i
yarn encore production
php bin/console d:s:u --force
php bin/console c:c --env=prod