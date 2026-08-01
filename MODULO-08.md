# Módulo 08 — XSS Reflected

## Ruta

`http://localhost:8081/xss-reflected.php`

## Objetivo académico

Demostrar cómo una entrada recibida mediante el parámetro GET `q` puede
insertarse directamente en la respuesta HTML cuando no se aplica codificación
de salida.

## Flujo

1. El usuario autenticado envía contenido mediante `q`.
2. PHP registra el intento en PostgreSQL.
3. La página imprime el contenido sin `htmlspecialchars()`.
4. El navegador interpreta las etiquetas y los eventos recibidos.

## Comprobación básica

Texto normal:

`Hola, Service Desk FIIS`

HTML interpretado:

`<strong>HTML interpretado</strong>`

Comprobación JavaScript local:

`<img src=x onerror="setTimeout(()=>{document.getElementById('xss-proof').textContent='XSS Reflected ejecutado'},0);this.remove()">`

## Evidencias recomendadas

- Formulario antes del envío.
- URL con el parámetro `q`.
- Resultado reflejado.
- Cambio del texto de comprobación.
- Cookie de laboratorio visible mediante `document.cookie`.
- Registro del intento en la tabla de historial.

## Alcance

El módulo fue creado para ejecutarse exclusivamente en `localhost` dentro del
laboratorio académico. No debe publicarse en Internet ni utilizar cookies o
datos reales.
