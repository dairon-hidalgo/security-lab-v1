param(
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot),
    [string]$Username = 'admin',
    [string]$Password = 'admin123'
)

$ErrorActionPreference = 'Stop'
Set-Location $ProjectRoot

function Get-EnvValue {
    param(
        [Parameter(Mandatory = $true)][string]$Name,
        [Parameter(Mandatory = $true)][string]$Default
    )

    $envFile = Join-Path $ProjectRoot '.env'

    if (-not (Test-Path $envFile)) {
        return $Default
    }

    $line = Get-Content $envFile |
        Where-Object { $_ -match "^$([regex]::Escape($Name))=" } |
        Select-Object -First 1

    if (-not $line) {
        return $Default
    }

    return ($line -split '=', 2)[1].Trim()
}

function Invoke-LabRequest {
    param(
        [Parameter(Mandatory = $true)][string]$Url,
        [Parameter(Mandatory = $true)]$Session
    )

    $response = Invoke-WebRequest `
        -Uri $Url `
        -WebSession $Session `
        -UseBasicParsing `
        -TimeoutSec 20

    if ($response.StatusCode -ne 200) {
        throw "HTTP inesperado en $Url`: $($response.StatusCode)"
    }

    Write-Host "[OK] $Url" -ForegroundColor Green
}

$appPort = Get-EnvValue -Name 'APP_PORT' -Default '8081'
$dbUser = Get-EnvValue -Name 'POSTGRES_USER' -Default 'labuser'
$dbName = Get-EnvValue -Name 'POSTGRES_DB' -Default 'security_lab'
$baseUrl = "http://localhost:$appPort"

Write-Host '1. Validando Docker Compose...'
docker compose config --quiet

if ($LASTEXITCODE -ne 0) {
    throw 'docker compose config encontró errores.'
}

Write-Host '2. Comprobando servicios...'
docker compose ps

docker compose exec -T db pg_isready -U $dbUser -d $dbName

if ($LASTEXITCODE -ne 0) {
    throw 'PostgreSQL no está disponible.'
}

Write-Host '3. Validando sintaxis PHP...'
docker compose exec -T app sh -lc `
    'find /var/www/html -path /var/www/html/uploads -prune -o -name "*.php" -type f -print0 | xargs -0 -n1 php -l'

if ($LASTEXITCODE -ne 0) {
    throw 'Se encontraron errores de sintaxis PHP.'
}

Write-Host '4. Iniciando sesión con la cuenta ficticia del laboratorio...'
$webSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Invoke-LabRequest -Url "$baseUrl/login.php" -Session $webSession

$loginResponse = Invoke-WebRequest `
    -Uri "$baseUrl/login.php" `
    -Method Post `
    -Body @{ username = $Username; password = $Password } `
    -WebSession $webSession `
    -UseBasicParsing `
    -TimeoutSec 20

if ($loginResponse.StatusCode -ne 200 -or $loginResponse.Content -notmatch 'Panel principal') {
    throw 'No fue posible autenticar la cuenta ficticia. Revisa usuario y contraseña.'
}

Write-Host '[OK] Sesión autenticada' -ForegroundColor Green

Write-Host '5. Comprobando los diez escenarios...'
$routes = @(
    '/dashboard.php',
    '/login-security.php',
    '/command.php',
    '/file-include.php',
    '/sqli.php?id=1',
    '/sqli-automated.php?id=1',
    '/blind-sqli.php?id=1',
    '/upload.php',
    '/xss-reflected.php',
    '/xss-stored.php',
    '/xss-dom.php'
)

foreach ($route in $routes) {
    Invoke-LabRequest -Url ($baseUrl + $route) -Session $webSession
}

Write-Host '6. Verificando tablas del laboratorio...'
$expectedTables = @(
    'users',
    'tickets',
    'login_attempts',
    'sqli_audit',
    'command_attempts',
    'file_include_attempts',
    'upload_attempts',
    'xss_reflected_attempts',
    'xss_stored_comments',
    'xss_cookie_captures',
    'xss_dom_captures'
)

$tableList = ($expectedTables | ForEach-Object { "'$($_)'" }) -join ','
$query = "SELECT tablename FROM pg_tables WHERE schemaname='public' AND tablename IN ($tableList) ORDER BY tablename;"

$found = docker compose exec -T db psql `
    -U $dbUser `
    -d $dbName `
    -At `
    -c $query

if ($LASTEXITCODE -ne 0) {
    throw 'No fue posible consultar las tablas de PostgreSQL.'
}

$foundTables = @($found | Where-Object { $_ -and $_.Trim() -ne '' } | ForEach-Object { $_.Trim() })
$missingTables = @($expectedTables | Where-Object { $_ -notin $foundTables })

if ($missingTables.Count -gt 0) {
    Write-Host 'Faltan las siguientes tablas:' -ForegroundColor Yellow
    $missingTables | ForEach-Object { Write-Host " - $_" }
    throw 'Ejecuta .\scripts\apply-migrations.ps1 y repite la verificación.'
}

Write-Host '[OK] Todas las tablas esperadas existen.' -ForegroundColor Green
Write-Host ''
Write-Host 'VALIDACIÓN COMPLETA: la V1 está operativa.' -ForegroundColor Green
Write-Host "Aplicación: $baseUrl"
