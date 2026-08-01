param(
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot)
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

$dbUser = Get-EnvValue -Name 'POSTGRES_USER' -Default 'labuser'
$dbName = Get-EnvValue -Name 'POSTGRES_DB' -Default 'security_lab'

Write-Host 'Comprobando el contenedor PostgreSQL...'
docker compose exec -T db pg_isready -U $dbUser -d $dbName

if ($LASTEXITCODE -ne 0) {
    throw 'PostgreSQL no está listo. Ejecuta primero: docker compose up -d'
}

$sqlFiles = Get-ChildItem (Join-Path $ProjectRoot 'database') -Filter '*.sql' |
    Sort-Object Name

if ($sqlFiles.Count -eq 0) {
    throw 'No se encontraron migraciones en la carpeta database.'
}

foreach ($file in $sqlFiles) {
    Write-Host "Aplicando: $($file.Name)"

    Get-Content $file.FullName -Raw |
        docker compose exec -T db psql `
            -v ON_ERROR_STOP=1 `
            -U $dbUser `
            -d $dbName

    if ($LASTEXITCODE -ne 0) {
        throw "Falló la migración: $($file.Name)"
    }
}

Write-Host ''
Write-Host 'Migraciones aplicadas correctamente.' -ForegroundColor Green
