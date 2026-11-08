.DEFAULT_GOAL := help
APP = docker compose exec app

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

up: ## Build and start all containers (http://localhost:6180)
	docker compose up -d --build

down: ## Stop containers (data is kept)
	docker compose down

reset: ## Stop containers AND delete the database volume
	docker compose down -v

logs: ## Follow application logs
	docker compose logs -f app web

shell: ## Open a shell inside the app container
	docker compose exec app bash

migrate: ## Run database migrations
	$(APP) php artisan migrate --force

fresh: ## Drop all tables, migrate and re-seed demo data
	$(APP) php artisan migrate:fresh --seed --force

test: ## Run the whole test-suite (unit + feature)
	$(APP) php artisan test

test-unit: ## Run unit tests only
	$(APP) php artisan test --testsuite=Unit

test-feature: ## Run feature tests only
	$(APP) php artisan test --testsuite=Feature

db-export: ## Export the database to database/sql/ecommerce.sql (submission file)
	docker compose exec mysql mysqldump -uroot -proot --no-tablespaces --routines laravel_ecommerce > database/sql/ecommerce.sql
	@echo "Exported to database/sql/ecommerce.sql"
