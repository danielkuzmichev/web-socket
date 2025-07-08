# Имя контейнера, можешь заменить под себя
APP_CONTAINER=websocket-server
REDIS_CONTAINER=redis

# Путь до скрипта импорта слов
IMPORT_SCRIPT=bin/import-word-in-redis.php

# Сборка контейнеров
build:
	docker compose build

up: 
	docker compose up

# Перезапуск контейнера с приложением
restart-app:
	docker compose restart $(APP_CONTAINER)

# Выполнение PHP скрипта импорта слов в Redis
import-words:
	docker compose exec $(APP_CONTAINER) php $(IMPORT_SCRIPT)

# Очистка Redis и повторный импорт
reset-redis:
	docker compose exec $(REDIS_CONTAINER) redis-cli FLUSHALL
	docker compose exec $(APP_CONTAINER) php $(IMPORT_SCRIPT)

# Вход в консоль Redis
redis-cli:
	docker compose exec $(REDIS_CONTAINER) redis-cli

# Вход в консоль приложения (PHP-контейнера)
app:
	docker compose exec $(APP_CONTAINER) bash

# Форматирование кода
cs-fix:
	docker compose exec $(APP_CONTAINER) ./vendor/bin/php-cs-fixer fix --allow-risky=yes --verbose

# Проверка стиля без изменений
cs-check:
	docker compose exec $(APP_CONTAINER) ./vendor/bin/php-cs-fixer fix --allow-risky=yes --verbose --dry-run

test:
	docker compose exec $(APP_CONTAINER) ./vendor/bin/phpunit tests

# Статический анализ
psalm:
	docker compose exec $(APP_CONTAINER) ./vendor/bin/psalm
