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
compile: tests ## Marathon phar compilation
	composer install --no-scripts --no-autoloader --no-dev
	composer dump-autoload --classmap-authoritative --no-dev --optimize
	php bin/console cache:clear --env=prod
	box compile
build: compile ## Build Marathon project

build-without-tests: ## Build Marathon project without running tests
	composer install --no-scripts --no-autoloader --no-dev
	composer dump-autoload --classmap-authoritative --no-dev --optimize
	php bin/console cache:clear --env=prod
	box compile
build-without-test: build-without-tests ## Build Marathon project without running tests
compile-without-tests: build-without-tests ## Build Marathon project without running tests
compile-without-test: build-without-tests ## Build Marathon project without running tests
