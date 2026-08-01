# Service Desk FIIS — Laboratorio de Seguridad V1

Aplicación académica deliberadamente vulnerable para ejecutar pruebas
exclusivamente en un entorno local y autorizado.

## Tecnologías

- Windows 11
- Docker Desktop
- Apache
- PHP 8.2
- PostgreSQL 16
- Git

## Levantar el proyecto

docker compose up -d --build

## Abrir la aplicación

http://localhost:8090

## Revisar contenedores

docker compose ps

## Ver registros

docker compose logs --tail=100 app
docker compose logs --tail=100 db

## Detener el proyecto

docker compose down

## Reiniciar completamente la base de datos

docker compose down -v
docker compose up -d --build