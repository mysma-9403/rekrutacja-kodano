# Plik: Makefile

up:
	@export $(shell grep -v '^#' symfony/.env.local | xargs) && docker compose up --build -d

down:
	docker down -v

restart:
	docker down -v && docker compose up --build -d

logs:
	docker logs -f

app-shell:
	docker exec app sh

nginx-shell:
	docker exec nginx sh

db-shell:
	docker exec db sh

rabbitmq-shell:
	docker exec rabbitmq sh

console:
	docker exec symfony_app php bin/console

composer-install:
	docker exec symfony_app composer install

migrate:
	docker exec symfony_app php bin/console doctrine:migrations:migrate --no-interaction

fixtures:
	docker exec symfony_app php bin/console doctrine:fixtures:load --no-interaction

cache-clear:
	docker exec symfony_app php bin/console cache:clear

phpunit:
	docker exec symfony_app php bin/phpunit

status:
	docker ps

phpstan:
	docker exec symfony_app vendor/bin/phpstan analyse

env-check:
	@echo "App: http://localhost:8080"
	@echo "Mailhog: http://localhost:8025"
	@echo "RabbitMQ UI: http://localhost:15672"
	@echo "MySQL: localhost:${MYSQL_PORT}"

help:
	@echo ""
	@echo "Dostępne komendy:"
	@echo "  make up             # uruchom kontenery"
	@echo "  make down           # zatrzymaj kontenery i usuń wolumeny"
	@echo "  make restart        # restartuj środowisko"
	@echo "  make logs           # logi kontenerów"
	@echo "  make app-shell      # wejście do kontenera aplikacji"
	@echo "  make console        # uruchom bin/console"
	@echo "  make composer-install # zainstaluj zależności PHP"
	@echo "  make migrate        # wykonaj migracje"
	@echo "  make fixtures       # załaduj dane testowe"
	@echo "  make cache-clear    # wyczyść cache"
	@echo "  make phpunit        # uruchom testy"
	@echo "  make status         # status kontenerów"
	@echo "  make env-check      # adresy usług"
	@echo ""
