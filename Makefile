up:
	docker-compose up -d
	docker-sync start

down:
	docker-compose down
	docker-sync stop

restart:
	make down
	make up

logs:
	docker-compose logs -f
