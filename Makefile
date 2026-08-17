SHELL := /bin/bash
APP := backend

.DEFAULT_GOAL := help

.PHONY: help up down restart install assets seed reset benchmark test ci lint types logs ps artisan

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

up: ## Build images and boot Tile38, the app, and the 1M-store seeder
	docker compose up -d --build

down: ## Stop all containers
	docker compose down

restart: down up ## Down, then up

install: ## Install backend deps and build frontend assets
	@docker compose run --rm app composer install --no-interaction --prefer-dist
	@cd $(APP) && npm ci
	@cd $(APP) && npm run build

assets: ## Build frontend assets (host npm)
	@cd $(APP) && npm run build

seed: ## Load the 1M-store dataset into Tile38
	@docker compose run --rm seed php artisan stores:seed --count=$${COUNT:-1000000} --force

reset: ## Drop the store collection and reseed it
	@docker compose run --rm seed php artisan stores:seed --count=$${COUNT:-1000000} --force

benchmark: ## Run a nearest-store benchmark
	@curl -s http://localhost:8000/api/stores/benchmark | jq

test: ## Run backend + frontend test suites
	@docker compose run --rm app php artisan test --testsuite=Feature
	@docker compose run --rm app php artisan test --testsuite=Integration
	@cd $(APP) && npm run test

ci: ## Full check: style, types, tests
	@docker compose run --rm app composer lint:check
	@docker compose run --rm app composer types:check
	@docker compose run --rm app php artisan test
	@cd $(APP) && npm run lint:check
	@cd $(APP) && npm run types:check
	@cd $(APP) && npm run test

lint: ## Pint style check
	@docker compose run --rm app composer lint:check

types: ## PHPStan type check
	@docker compose run --rm app composer types:check

logs: ## Tail app logs
	@docker compose logs -f app

ps: ## Container status
	@docker compose ps

artisan: ## Run an artisan command, e.g. make artisan C="tinker"
	@docker compose exec app php artisan $(C)
