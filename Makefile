up:
	docker-compose up -d
	docker-sync start

down:
	docker-compose down
	docker-sync stop

logs:
	docker-compose logs -f
