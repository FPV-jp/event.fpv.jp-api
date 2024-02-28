init:
	cat .env.example > .env
	composer install

run:
	composer serve

up:
	docker compose up --build

up-d:
	docker compose up -d --build

down:
	docker compose down

destroy:
	docker system prune --all --force --volumes

restart-api: down up
