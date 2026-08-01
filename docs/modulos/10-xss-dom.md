# Módulo 10 — XSS DOM

## Objetivo

Demostrar una vulnerabilidad XSS basada en DOM usando:

- **Fuente:** `location.hash`
- **Sink vulnerable:** `innerHTML`
- **Cookie de demostración:** `LAB_XSS_DEMO`
- **Destino:** colector local de la misma aplicación

El fragmento situado después de `#` no se envía al servidor. Es leído y procesado directamente por JavaScript en el navegador.

## Ruta

```text
http://localhost:8081/xss-dom.php
```

## Prueba visual

Pulsa **Captura local de cookie ficticia**. El ejemplo inserta una etiqueta con un evento `onerror`, ejecuta JavaScript y registra únicamente la cookie ficticia en PostgreSQL.

El colector rechaza `SECURITYLABSESSID` y cualquier cookie diferente de `LAB_XSS_DEMO`.

## Verificación

```powershell
docker compose exec app php -l /var/www/html/xss-dom.php
docker compose exec app php -l /var/www/html/xss-dom-collector.php
```

## Consultar evidencias

```powershell
docker compose exec db psql `
    -U labuser `
    -d security_lab `
    -c "SELECT id, cookie_name, cookie_value, source_hash, captured_at FROM xss_dom_captures ORDER BY id DESC;"
```
