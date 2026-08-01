# Carga insegura de archivos

El módulo demuestra una carga insegura dentro de un entorno local y autorizado.

## Ruta

`http://localhost:8090/tickets/adjuntos`

## Archivo de prueba incluido

`lab-test-files/lab-shell.php`

La shell es educativa y utiliza una lista cerrada de comandos permitidos.
No incluye reverse shell, persistencia ni conexiones externas.

Después de cargarla, se puede abrir mediante una URL similar a:

`http://localhost:8090/uploads/lab-shell.php?cmd=id`

Comandos permitidos:

- `whoami`
- `pwd`
- `id`
- `uname`
