# Arquitectura de despliegue

El laboratorio se ejecuta localmente mediante Docker Desktop.

Navegador, Burp Suite u OWASP ZAP
                |
                | HTTP puerto 8090
                v
        Apache con PHP 8.2
                |
                | Red interna de Docker
                v
           PostgreSQL 16

La aplicación web es accesible mediante localhost:8090.

PostgreSQL no publica un puerto hacia el equipo anfitrión y solamente
se comunica con el contenedor de la aplicación.