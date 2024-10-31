PWD := $(shell pwd -L)
PHP_IMAGE := gustavofreze/php:8.2
APP_RUN := docker run -u root --rm -it --network=host -v ${PWD}:/app -w /app ${PHP_IMAGE}
APP_TEST_RUN := docker run -u root --rm -it --name account-test -v ${PWD}:/app -v /var/run/docker.sock:/var/run/docker.sock -w /app ${PHP_IMAGE}

.DEFAULT_GOAL := help

.PHONY: configure test unit-test review fix-style show-reports help

configure: ## Configure development environment
	@${APP_RUN} composer update --optimize-autoloader

test: ## Run all tests
	@${APP_TEST_RUN} composer run tests

unit-test: ## Run unit tests
	@${APP_RUN} composer run unit-test

review: ## Run code review
	@${APP_RUN} composer review

fix-style: ## Fix code style
	@${APP_RUN} composer fix-style

show-reports: ## Open static analysis reports (e.g., coverage, lints) in the browser
	@sensible-browser report/coverage/coverage-html/index.html report/coverage/mutation-report.html

help: ## Display this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "Setup and run"
	@grep -E '^(configure):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Testing"
	@grep -E '^(test|unit-test):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Code review"
	@grep -E '^(review|fix-style):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Reports"
	@grep -E '^(show-reports):.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""
