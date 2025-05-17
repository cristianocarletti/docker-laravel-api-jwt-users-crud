# Laravel Docker Setup

Este projeto é um ambiente completo de desenvolvimento para **Laravel** usando **Docker**, com suporte a:

- PHP 8.1 (FPM)
- MySQL 8.0
- Redis
- Nginx (como proxy reverso)
- phpMyAdmin
- Certbot (para HTTPS automático, opcional)

## 🚀 Requisitos

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

📦 Subindo o ambiente

```bash
git clone https://github.com/cristianocarletti/docker-laravel-api-jwt-users-crud.git
cd docker-laravel-api-jwt-users-crud

1. cp .env.example .env

2. docker-compose up --build -d

3. docker-compose exec laravel composer install

4. docker-compose exec laravel php artisan key:generate

5. docker-compose exec laravel php artisan jwt:secret

6. docker-compose exec laravel php artisan migrate:fresh --seed

7. docker-compose exec laravel bash

8. chmod -R 775 storage bootstrap/cache

9. chown -R www-data:www-data storage bootstrap/cache

10. docker-compose exec laravel php artisan l5-swagger:generate


## Acessando o ambiente

Aplicação Laravel: http://localhost:8080

phpMyAdmin: http://localhost:8081
Credenciais:
Usuário: laravel
Senha: secret

Documentação http://localhost:8080/api/documentation

⚙️ Configurações do Laravel Aqui estão as configurações recomendadas para o arquivo .env

APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:randomstring
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3308
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

🛠️ Comandos úteis
Para acessar o shell do container Laravel e rodar comandos Artisan, execute:
docker-compose exec laravel bash

Dentro do container, você pode rodar diversos comandos do Laravel:
# Rodar migrations
docker-compose exec laravel php artisan migrate

# Criar um novo usuário admin via tinker
docker-compose exec laravel php artisan tinker
App\Models\User::create(['name' => 'Admin', 'email' => 'admin@user.com', 'password' => bcrypt('password123')]);

# Limpar cache
docker-compose exec laravel php artisan config:clear

# Atualizar cache
docker-compose exec laravel php artisan config:cache

# optimize
docker-compose exec laravel php artisan optimize:clear

Gerenciando os containers
# Parar todos os containers
docker-compose down

# Subir os containers novamente
docker-compose up

🔧 Configuração de Produção
docker-compose exec ssl certbot --nginx

Executar testes:
docker-compose exec laravel php artisan test
