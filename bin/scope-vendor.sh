set -e

if [[ ! -e bin/php-scoper.phar ]]; then
  echo " > Downloading php-scoper.phar"
  echo ""

  curl -sSL -o bin/php-scoper.phar https://github.com/humbug/php-scoper/releases/download/0.14.0/php-scoper.phar
fi

echo " > Making sure composer vendor files are on the locked version"
echo ""

# Install the dependencies (as defined in the composer.lock) first so we can package them up
composer install --no-dev --no-interaction --no-progress

echo ""
echo " > Scoping the PHP files to prevent conflicts with other plugins"

php bin/php-scoper.phar add-prefix -s -q --force

echo " > Patching composer.json for scoped autoloader"

sed -i -e 's/src\\\//..\/src\//g' ./build/composer.json

# This is fixing OS X sed and unix sed being slightly different
# OS X sed generates a composer.json-e file we don't need
# For more info: https://unix.stackexchange.com/a/131940
if [[ -e ./build/composer.json-e ]]; then
  rm ./build/composer.json-e
fi

echo " > Dumping new composer autoloader for scoped vendor"
echo ""

# Running this in a subshell to not mess with the current working directory
(cd build && composer dump-autoload --classmap-authoritative --no-interaction)

php ./bin/scope-vendor-fix.php
