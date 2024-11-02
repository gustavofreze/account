ifeq ($(OS),Windows_NT)
    PWD := $(shell pwd -L)
else
    PWD := $(shell pwd -L)
endif

PHP_IMAGE = gustavofreze/php:8.2
FLYWAY_IMAGE = flyway/flyway:10.20.1

APP_RUN = docker run -u root --rm -it --network=host -v "$${PWD}:/app" -w /app ${PHP_IMAGE}
APP_TEST_RUN = docker run -u root --rm -it --name account-test --link account-adm --network=account_default -v "$${PWD}:/app" -w /app ${PHP_IMAGE}

FLYWAY_RUN = docker run --rm -v "$${PWD}/config/database/mysql/migrations:/flyway/sql" --env-file=config/local.env --network=account_default ${FLYWAY_IMAGE}
MIGRATE_DB = ${FLYWAY_RUN} -locations=filesystem:/flyway/sql -schemas=account_adm -connectRetries=15
MIGRATE_TEST_DB = ${FLYWAY_RUN} -locations=filesystem:/flyway/sql -schemas=account_adm_test -connectRetries=15

.DEFAULT_GOAL := help
.PHONY: start configure migrate-database clean-database migrate-test-database test test-no-coverage review fix-style show-reports help

start: ## Start Docker compose services
	@docker-compose up -d --build

configure: ## Configure development environment
	@${APP_RUN} composer update --optimize-autoloader

test: migrate-test-database ## Run all tests with coverage
	@${APP_TEST_RUN} composer run tests

test-no-coverage: migrate-test-database ## Run all tests without coverage
	@${APP_TEST_RUN} composer run tests-no-coverage

review: ## Run static code analysis
	@${APP_RUN} composer review

fix-style: ## Fix code style
	@${APP_RUN} composer fix-style

show-reports: ## Open static analysis reports (e.g., coverage, lints) in the browser
	@sensible-browser report/coverage/coverage-html/index.html report/coverage/mutation-report.html

migrate-database: ## Run database migrations
	@${MIGRATE_DB} migrate

clean-database: ## Clean database
	@${MIGRATE_DB} clean

migrate-test-database: ## Run test database migrations
	@${MIGRATE_TEST_DB} migrate

help: ## Display this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "Setup and run"
	@grep -E '^(start|configure|migrate-database|clean-database):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Testing"
	@grep -E '^(test|test-no-coverage):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Code review"
	@grep -E '^(review|fix-style):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Reports"
	@grep -E '^(show-reports):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Help"
	@grep -E '^(help):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
