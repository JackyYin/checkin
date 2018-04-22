## Docker 
- docker build --rm -t larvata-checkin .
- docker-compose up -d
- docker exec -it larvata-checkin composer install --no-progress --profile

## 複製設定檔
- cp .env.sample .env

## 更改權限
- chmod -R 775 storage bootstrap/cache

## Swagger-ui
- localhost:7778/swagger-ui
