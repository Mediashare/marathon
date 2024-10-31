##
##Cache
##
cache-clear: ## Cache clear
	php bin/console cache:clear
cache-warmup: ## Cache warmup
	php bin/console cache:warmup

cache: cache-clear cache-warmup
clear: cache-clear cache-warmup

##
##Tests
##
tests: ## Run PHPUnit tests
	composer install
	php bin/phpunit
.PHONY: tests
test: tests ## Run PHPUnit tests
.PHONY: test

##
##Build
##
compile: cache tests ## Marathon phar compilation
	composer install --no-scripts --no-autoloader --no-dev
	composer dump-autoload --classmap-authoritative --no-dev --optimize
	box compile

build: compile ## Build Marathon project

build-without-tests: cache ## Build Marathon project without running tests
	composer install --no-scripts --no-autoloader --no-dev
	composer dump-autoload --classmap-authoritative --no-dev --optimize
	box compile

build-without-test: build-without-tests ## Build Marathon project without running tests
compile-without-tests: build-without-tests ## Build Marathon project without running tests
compile-without-test: build-without-tests ## Build Marathon project without running tests
