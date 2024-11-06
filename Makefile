ifeq ($(OS),Windows_NT)
    PWD := $(shell cd)
else
    PWD := $(shell pwd -L)
endif

ARCH := $(shell uname -m)
PLATFORM :=

ifeq ($(ARCH),arm64)
    PLATFORM := --platform=linux/amd64
endif

PHP_IMAGE = gustavofreze/php:8.3
FLYWAY_IMAGE = flyway/flyway:10.20.1

APP_RUN = docker run ${PLATFORM} -u root --rm -it -v ${PWD}:/app -w /app ${PHP_IMAGE}
APP_TEST_RUN = docker run ${PLATFORM} -u root --rm -it --name account-test --link account-adm --network=account_default -v ${PWD}:/app -w /app ${PHP_IMAGE}

FLYWAY_RUN = docker run ${PLATFORM} --rm -v ${PWD}/config/database/mysql/migrations:/flyway/sql --env-file=config/local.env --network=account_default ${FLYWAY_IMAGE}
MIGRATE_DB = ${FLYWAY_RUN} -locations=filesystem:/flyway/sql -schemas=account_adm -connectRetries=15
MIGRATE_TEST_DB = ${FLYWAY_RUN} -locations=filesystem:/flyway/sql -schemas=account_adm_test -connectRetries=15

.DEFAULT_GOAL := help
.PHONY: start stop configure migrate-database clean-database migrate-test-database test test-no-coverage review show-reports help show-logs

start: ## Start application containers
	@docker compose up -d --build

stop: ## Stop application containers
	@docker compose down

configure: ## Configure development environment
	@${APP_RUN} composer update --optimize-autoloader

test: migrate-test-database ## Run all tests with coverage
	@${APP_TEST_RUN} composer run tests

test-no-coverage: migrate-test-database ## Run all tests without coverage
	@${APP_TEST_RUN} composer run tests-no-coverage

review: ## Run static code analysis
	@${APP_RUN} composer review

show-reports: ## Open static analysis reports (e.g., coverage, lints) in the browser
	@sensible-browser report/coverage/coverage-html/index.html report/coverage/mutation-report.html

migrate-database: ## Run database migrations
	@${MIGRATE_DB} migrate

clean-database: ## Clean database
	@${MIGRATE_DB} clean

migrate-test-database: ## Run test database migrations
	@${MIGRATE_TEST_DB} migrate

show-logs: ## Display application logs
	@docker logs -f account

help: ## Display this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "Setup and run"
	@grep -E '^(start|stop|configure|migrate-database|clean-database):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Testing"
	@grep -E '^(test|test-no-coverage):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Code review"
	@grep -E '^(review):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Reports"
	@grep -E '^(show-reports):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Observability"
	@grep -E '^(show-logs):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Help"
	@grep -E '^(help):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
