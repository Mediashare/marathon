##
##Install
##
install-opti: ## Composer install optimized
	composer install --no-scripts --no-autoloader --no-dev
	composer dump-autoload --classmap-authoritative --no-dev --optimize
install: ## Composer install
	composer install --no-scripts --no-autoloader
	composer dump-autoload --classmap-authoritative --optimize

##
##Cache
##
cache-clear: ## Cache clear
	php bin/console cache:clear
cache: cache-clear
clear: cache-clear
cache-warmup: ## Cache warmup
	php bin/console cache:warmup
warmup: cache-warmup

##
##Tests
##
test: install ## Run PHPUnit tests
	chmod +x bin/phpunit
	php bin/phpunit
.PHONY: test
tests: test ## Run PHPUnit tests
.PHONY: test
phpunit: test ## Run PHPUnit tests

##
##Build
##
compile: tests install-opti cache warmup ## Build Marathon project
	box compile
build: compile ## Build Marathon project

build-prod: tests install-opti cache warmup ## Build Marathon project
	box compile

build-without-tests: install-opti cache warmup ## Build Marathon project without running tests
	box compile
build-without-test: build-without-tests ## Build Marathon project without running tests
compile-without-tests: build-without-tests ## Build Marathon project without running tests
compile-without-test: build-without-tests ## Build Marathon project without running tests
