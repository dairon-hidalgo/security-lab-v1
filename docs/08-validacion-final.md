# Validación final de la V1

Esta fase confirma que la aplicación vulnerable V1 puede levantarse de forma reproducible en Windows 11 con Docker Desktop.

## Alcance

La validación comprueba:

- configuración de Docker Compose;
- disponibilidad de Apache/PHP y PostgreSQL;
- sintaxis de todos los archivos PHP;
- autenticación con una cuenta ficticia;
- respuesta HTTP de los diez escenarios;
- existencia de las tablas utilizadas por los módulos;
- estado del módulo 10 en el panel principal.

## Aplicar migraciones en un proyecto existente

Los archivos SQL se ejecutan automáticamente únicamente cuando PostgreSQL crea un volumen nuevo. Para actualizar una base ya existente:

```powershell
.\scripts\apply-migrations.ps1
```

Este script es idempotente: utiliza `CREATE TABLE IF NOT EXISTS` y puede ejecutarse más de una vez.

## Verificación automatizada

```powershell
.\scripts\verify-v1.ps1
```

Credenciales ficticias predeterminadas:

```text
admin / admin123
```

Cuando la cuenta haya sido modificada:

```powershell
.\scripts\verify-v1.ps1 -Username "USUARIO" -Password "CONTRASENA"
```

El resultado esperado es:

```text
VALIDACIÓN COMPLETA: la V1 está operativa.
```

## Instalación limpia

Para una instalación nueva, la carpeta completa `database` se monta en `/docker-entrypoint-initdb.d`. PostgreSQL ejecuta los archivos `.sql` en orden al crear el volumen por primera vez.

```powershell
Copy-Item .env.example .env

docker compose up -d --build
.\scripts\verify-v1.ps1
```

## Reinicio total de la base

Este procedimiento elimina todos los registros del laboratorio:

```powershell
docker compose down -v
docker compose up -d --build
```

Debe utilizarse únicamente cuando se requiera comenzar las evidencias desde cero.

## Evidencia recomendada

Conservar una captura de:

1. `docker compose ps`;
2. resultado final de `verify-v1.ps1`;
3. dashboard con los diez módulos implementados;
4. una prueba controlada por módulo;
5. tablas de auditoría en PostgreSQL;
6. historial de Git y etiqueta final.

## Cierre en Git

```powershell
git status
git add .
git commit -m "chore: stabilize database migrations and add final validation"
git tag -a v1.0.0-vulnerable -m "Service Desk FIIS vulnerable laboratory V1"
git log --oneline --decorate -10
```

El repositorio debe mantenerse privado mientras contenga código deliberadamente vulnerable.
