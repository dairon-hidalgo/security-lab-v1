# Service Desk FIIS — Laboratorio de Seguridad V1

Aplicación **deliberadamente vulnerable**
## Tabla de contenidos

- [Stack tecnológico](#stack-tecnológico)
- [Arquitectura de despliegue](#arquitectura-de-despliegue)
- [Contenedores](#contenedores)
- [Red](#red)
- [Volúmenes](#volúmenes)
- [Variables de entorno](#variables-de-entorno)
- [Esquema de base de datos](#esquema-de-base-de-datos)
- [Módulos y vulnerabilidades](#módulos-y-vulnerabilidades)
- [Puesta en marcha](#puesta-en-marcha)
- [Uso](#uso)
- [Guía de ataques](#guía-de-ataques)
- [Referencias de interés](#referencias-de-interés)

---

---

## Puesta en marcha
Asegurarse de tener Docker Corriendo 
Asegurarse de tener la env (por si acaso)
Asegurarse de tener los puertos indicados libres (8081(v1) y 8082 (v2)), si no, cambiarlos en el env de cada versión por otros que SI estén libres 

```bash

# Levantar todos los servicios
docker compose up -d --build

# Verificar estado
docker compose ps
```

## Uso

| Acción | Comando |
|---|---|
| Abrir la aplicación | `http://localhost:8081` |
| Detener | `docker compose down` |
| Reinicio limpio (No creo que sea necesario, pero por si acaso) | `docker compose down -v && docker compose up -d --build` |

CREDENCIALES 
| Usuario    | Contraseña | Nombre             |
| `admin`    | `admin123` | Administrador FIIS 
| `analista` | `fiis2026` | Analista de Soporte 
| `usuario`  | `password` | Usuario de Prueba 

> Las contraseñas se almacenan en texto plano
---
## Stack tecnológico

| Componente | Tecnología | Versión | Función |
|---|---|---|---|
| Runtime PHP | PHP + Apache | 8.2 / 2.4 | Servidor de aplicación web |
| Base de datos | PostgreSQL | 16 | Almacenamiento relacional |
| Servidor estático | Nginx | Alpine | Fuente de archivos para RFI |
| Orquestación | Docker Compose | v2+ | Despliegue multi-contenedor |
| Host | Windows / Linux | — | Docker Desktop o Docker Engine |

---

## Arquitectura de despliegue

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              Docker Host                                    │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                      security_lab_network (bridge)                   │   │
│  │                                                                      │   │
│  │  ┌─────────────────────────────────────────────────────────────┐     │   │
│  │  │            security-lab-app (php:8.2-apache)                │     │   │
│  │  │                                                             │     │   │
│  │  │  ● PHP 8.2.33 + Apache 2.4.68                               │     │   │
│  │  │  ● Extensiones: pdo_pgsql, pgsql                            │     │   │
│  │  │  ● Módulos Apache: rewrite, headers                         │     │   │
│  │  │  ● Bind mount: ./app → /var/www/html                        │     │   │
│  │  │  ● Puerto expuesto: 8081:80                                 │     │   │
│  │  └──────────────────────────────────┬──────────────────────────┘     │   │
│  │                                     │                                │   │
│  │  ┌──────────────────────┐           │      ┌────────────────────┐    │   │
│  │  │    security-lab-db   │           │      │ security-lab-rfi-  │    │   │
│  │  │     (postgres:16)    │◄──────────┘      │ source             │    │   │
│  │  │                      │                  │  (nginx:alpine)    │    │   │
│  │  │  ● Puerto: 5432      │                  │                    │    │   │
│  │  │    (interno)         │                  │  ● Puerto: 80      │    │   │
│  │  │  ● Volume:           │                  │    (interno)       │    │   │
│  │  │    postgres_data     │                  │  ● Bind mount:     │    │   │
│  │  │  ● Init scripts:     │                  │    ./rfi-source →  │    │   │
│  │  │    ./database/*.sql  │                  │   /usr/share/nginx/│    │   │
│  │  └──────────────────────┘                  │    html (ro)       │    │   │
│  │                                            └────────────────────┘    │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│              Puerto expuesto al host: 8090 (solo la app)                    │
└─────────────────────────────────────────────────────────────────────────────┘

Flujo de conexión:

Usuario (Kali / Windows)
    │
    ▼ HTTP :8081
security-lab-app (Apache + PHP)
    │
    ├──► security-lab-db (PostgreSQL :5432)
    │         └──► security_lab.users
    │
    └──► security-lab-rfi-source (Nginx :80)
```

### Características del despliegue

- **Aislamiento**: la BD y la fuente RFI no están expuestas al host; solo la
  app publica el puerto 8081.
- **Persistencia**: los datos viven en un *named volume* (`postgres_data`),
  osea que sobreviven a reinicios
- **Edición rápida**: Para acelerar el proceso de edición, se ha optado por un bind mount `./app → /var/www/html`, el cual permite modificar
  código sin necesidad de reconstruir el contenedor
- **DB levanta al iniciar**: los scripts `./database/*.sql` se ejecutan la
  primera vez que arranca la BD.

---

## Contenedores

### 1. `security-lab-app` — Aplicación principal

| Atributo | Valor |
|---|---|
| Imagen | `php:8.2-apache`  |
| Puerto | `8081:80`  |
| Bind mount | `./app → /var/www/html` |
| Extensiones PHP | `pdo_pgsql`, `pgsql` |
| Módulos Apache | `rewrite`, `headers` |

Configuración PHP insegura (`app/php-v1.ini`):

```ini
allow_url_fopen   = On     ; permite LFI/RFI por URL
allow_url_include = On     ; permite incluir archivos remotos
display_errors    = On     ; expone mensajes de error SQL
error_reporting   = E_ALL
```

### 2. `security-lab-db` — Base de datos

| Atributo | Valor |
|---|---|
| Imagen | `postgres:16` |
| Puerto | `5432` |
| Volume | `postgres_data` |
| Init scripts | `./database/*.sql` (montados `ro`) |

### 3. `security-lab-rfi-source` — Fuente de archivos remotos

| Atributo | Valor |
|---|---|
| Imagen | `nginx:alpine` |
| Puerto | `80` |
| Bind mount | `./rfi-source → /usr/share/nginx/html` (ro) |

---

## Red

| Atributo | Valor |
|---|---|
| Nombre | `security_lab_network` |
| Driver | `bridge` |
| Comunicación | interna entre los 3 contenedores |
| Exposición | Puerto `8081` |

---

## Volúmenes

| Recurso | Tipo | Montaje | Modo |
|---|---|---|---|
| `postgres_data` | Named volume | Datos de PostgreSQL | rw |
| `./app` | Bind mount | `/var/www/html` | rw |
| `./rfi-source` | Bind mount | `/usr/share/nginx/html` | ro |
| `./database` | Bind mount | `/docker-entrypoint-initdb.d` | ro |

---

## Variables de entorno

Archivo `.env`:

```env
POSTGRES_DB=security_lab
POSTGRES_USER=labuser
POSTGRES_PASSWORD=
APP_PORT=8081
```

Variables inyectadas en cada contenedor:

| Variable | Valor | Contenedor |
|---|---|---|
| `POSTGRES_DB` | `security_lab` | db |
| `POSTGRES_USER` | `labuser` | db |
| `POSTGRES_PASSWORD` | `panzoya` | db |
| `DB_HOST` | `db` | app |
| `DB_PORT` | `5432` | app |
| `DB_NAME` | `security_lab` | app |
| `DB_USER` | `labuser` | app |
| `DB_PASSWORD` | `` | app |
| `APP_PORT` | `8090` | host |

---

## Esquema de base de datos

```
security_lab 
│
├── users
│   ├── id         SERIAL PRIMARY KEY
│   ├── username   VARCHAR(50)  UNIQUE NOT NULL
│   ├── password   VARCHAR(255) NOT NULL   ← TEXTO PLANO
│   ├── full_name  VARCHAR(100) NOT NULL
│   ├── role       VARCHAR(20)  DEFAULT 'user'
│   └── created_at TIMESTAMP    DEFAULT NOW()
│
├── tickets
│   ├── id          SERIAL PRIMARY KEY
│   ├── user_id     INTEGER REFERENCES users(id)
│   ├── title       VARCHAR(150)
│   ├── description TEXT
│   ├── status      VARCHAR(30) DEFAULT 'abierto'
│   └── created_at  TIMESTAMP   DEFAULT NOW()
│
├── login_attempts            (02-login-attempts.sql)
├── sqli_attempts             (02-sqli.sql)
├── command_attempts          (03-command-attempts.sql)
├── file_include_attempts     (04-file-include-attempts.sql)
├── upload_attempts           (05-upload-attempts.sql)
├── xss_reflected_attempts    (06-xss-reflected-attempts.sql)
├── xss_stored_attempts       (07-xss-stored.sql)
├── xss_dom_attempts          (08-xss-dom.sql)
└── more_users                (09-more-users.sql)
```

### Datos iniciales (`database/01-init.sql`)


---

## Módulos y vulnerabilidades

| Ruta | Módulo | Vulnerabilidad |
|---|---|---|
| `/directorio/usuarios.php` | Directorio | SQLi UNION (4 columnas) |
| `/directorio/verificar.php` | Directorio | SQLi blind (boolean + time) |
| `/directorio/consulta.php` | Directorio | SQLi error-based / directo |
| `/soporte/buscar.php` | Soporte | SQLi UNION + XSS reflected |
| `/soporte/comentarios.php` | Soporte | XSS stored |
| `/soporte/documentacion.php` | Soporte | XSS stored |
| `/red/diagnostico.php` | Red | RCE |
| `/seguridad/accesos.php` | Seguridad |  |
| `/tickets/adjuntos.php` | Tickets | File upload inseguro |
| `/api/capturar-cookie.php` | API | XSS DOM |
| `/login.php` | Autenticación | Brute Force |

## Guía de ataques(ya en el informe)
