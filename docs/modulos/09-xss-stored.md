# XSS Stored

## Objetivo

Demostrar que un contenido almacenado en PostgreSQL puede ejecutarse cada vez
que otro usuario visita la página cuando la aplicación lo imprime sin
codificación de salida.

## Ruta

```text
http://localhost:8090/soporte/comentarios
```

## Flujo del escenario

1. El usuario autenticado publica un comentario.
2. La aplicación guarda el contenido en `xss_stored_comments`.
3. La página recupera el comentario y lo inserta directamente en el HTML.
4. El navegador interpreta las etiquetas o eventos incluidos.
5. La demostración de cookie envía únicamente `LAB_XSS_DEMO` al colector local
   `/api/capturar-cookie`.

## Límites de seguridad del laboratorio

- El escenario debe ejecutarse únicamente en `localhost`.
- El colector rechaza cualquier cookie diferente de `LAB_XSS_DEMO`.
- No existe comunicación con servidores externos.
- No se almacena la cookie de sesión del usuario.

## Restauración

El usuario administrador puede utilizar el botón **Reiniciar módulo** para
eliminar comentarios y capturas de demostración.
