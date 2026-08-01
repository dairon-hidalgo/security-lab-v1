# HTTPS y cabeceras de seguridad — V2

La versión segura publica dos puertos locales:

- `http://localhost:8082`: redirección permanente.
- `https://localhost:8443`: aplicación segura.

El certificado se genera durante la construcción de la imagen con OpenSSL y
contiene los SAN `localhost` y `127.0.0.1`. Al ser autofirmado, el navegador
mostrará una advertencia de confianza apropiada para el laboratorio local.

Cabeceras incorporadas:

- Content-Security-Policy
- X-Content-Type-Options
- X-Frame-Options
- Referrer-Policy
- Permissions-Policy
- Cross-Origin-Opener-Policy
- Cross-Origin-Resource-Policy

HSTS se omite intencionalmente porque la V1 vulnerable se ejecuta mediante
HTTP en el mismo hostname (`localhost:8081`). HSTS se aplica por hostname y
no por puerto, por lo que activarlo impediría comparar ambas versiones en el
mismo navegador.
