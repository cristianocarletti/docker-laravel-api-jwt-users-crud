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

## 📦 Subindo o ambiente

Clone este repositório para sua máquina local:

```bash
git clone https://github.com/cristianocarletti/docker-laravel-api-jwt-users-crud.git
cd docker-laravel-api-jwt-users-crud

```bash
docker-compose up --build

## Acessando o ambiente

Aplicação Laravel: http://localhost:8080

phpMyAdmin: http://localhost:8081
Credenciais:
Usuário: root
Senha: root

## ⚙️ Configurações do Laravel Aqui estão as configurações recomendadas para o arquivo .env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:randomstring
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

## 🛠️ Comandos úteis
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

## Gerenciando os containers
# Parar todos os containers
docker-compose down

# Subir os containers novamente
docker-compose up

## 🔧 Configuração de Produção
docker-compose exec ssl certbot --nginx
