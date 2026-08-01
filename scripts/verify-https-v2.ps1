$ErrorActionPreference = "Stop"

function Show-Result {
    param(
        [string]$Name,
        [bool]$Passed,
        [string]$Detail
    )

    if ($Passed) {
        Write-Host "[OK]   $Name" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Name" -ForegroundColor Red
    }

    if ($Detail -ne "") {
        Write-Host "       $Detail"
    }
}

function Contains-Header {
    param(
        [string]$Headers,
        [string]$Pattern
    )

    $options = (
        [System.Text.RegularExpressions.RegexOptions]::IgnoreCase `
        -bor
        [System.Text.RegularExpressions.RegexOptions]::Multiline
    )

    return [regex]::IsMatch(
        $Headers,
        $Pattern,
        $options
    )
}

Write-Host ""
Write-Host "============================================="
Write-Host " Service Desk FIIS - Validacion HTTPS V2"
Write-Host "============================================="
Write-Host ""

Write-Host "1. Estado de Docker Compose"
Write-Host ""

$composeOutput = docker compose -p security-lab-v2 ps 2>&1
$composeExitCode = $LASTEXITCODE

Show-Result `
    -Name "Docker Compose responde" `
    -Passed ($composeExitCode -eq 0) `
    -Detail ($composeOutput -join "`n")

if ($composeExitCode -ne 0) {
    exit 1
}

Write-Host ""
Write-Host "2. Redireccion HTTP hacia HTTPS"
Write-Host ""

$httpOutput = curl.exe `
    -sS `
    -D - `
    -o NUL `
    "http://localhost:8082/login.php" 2>&1

$httpExitCode = $LASTEXITCODE
$httpHeaders = $httpOutput -join "`n"

$httpStatusOk = Contains-Header `
    -Headers $httpHeaders `
    -Pattern '^HTTP/\S+\s+(301|302|307|308)\b'

$locationOk = Contains-Header `
    -Headers $httpHeaders `
    -Pattern '^Location:\s*https://localhost:8443/'

Show-Result `
    -Name "Puerto HTTP 8082 responde" `
    -Passed ($httpExitCode -eq 0) `
    -Detail ""

Show-Result `
    -Name "HTTP devuelve redireccion" `
    -Passed $httpStatusOk `
    -Detail ""

Show-Result `
    -Name "Redireccion apunta a HTTPS 8443" `
    -Passed $locationOk `
    -Detail ""

Write-Host ""
Write-Host "3. Servicio HTTPS"
Write-Host ""

$httpsOutput = curl.exe `
    -k `
    -sS `
    -D - `
    -o NUL `
    "https://localhost:8443/login.php" 2>&1

$httpsExitCode = $LASTEXITCODE
$httpsHeaders = $httpsOutput -join "`n"

$httpsStatusOk = Contains-Header `
    -Headers $httpsHeaders `
    -Pattern '^HTTP/\S+\s+(200|302)\b'

Show-Result `
    -Name "HTTPS 8443 responde" `
    -Passed ($httpsExitCode -eq 0 -and $httpsStatusOk) `
    -Detail ""

Write-Host ""
Write-Host "4. Cabeceras de seguridad"
Write-Host ""

$headerTests = @(
    @{
        Name = "Content-Security-Policy"
        Pattern = '^Content-Security-Policy:'
    },
    @{
        Name = "X-Content-Type-Options"
        Pattern = '^X-Content-Type-Options:\s*nosniff'
    },
    @{
        Name = "X-Frame-Options"
        Pattern = '^X-Frame-Options:\s*(DENY|SAMEORIGIN)'
    },
    @{
        Name = "Referrer-Policy"
        Pattern = '^Referrer-Policy:'
    },
    @{
        Name = "Permissions-Policy"
        Pattern = '^Permissions-Policy:'
    },
    @{
        Name = "Cross-Origin-Opener-Policy"
        Pattern = '^Cross-Origin-Opener-Policy:\s*same-origin'
    }
)

$headersPassed = $true

foreach ($test in $headerTests) {
    $passed = Contains-Header `
        -Headers $httpsHeaders `
        -Pattern $test.Pattern

    Show-Result `
        -Name $test.Name `
        -Passed $passed `
        -Detail ""

    if (-not $passed) {
        $headersPassed = $false
    }
}

Write-Host ""
Write-Host "5. Cookie de sesion"
Write-Host ""

$cookiePresent = Contains-Header `
    -Headers $httpsHeaders `
    -Pattern '^Set-Cookie:\s*SECURITYLABV2SESSID='

$cookieSecure = Contains-Header `
    -Headers $httpsHeaders `
    -Pattern '^Set-Cookie:.*\bSecure\b'

$cookieHttpOnly = Contains-Header `
    -Headers $httpsHeaders `
    -Pattern '^Set-Cookie:.*\bHttpOnly\b'

$cookieSameSite = Contains-Header `
    -Headers $httpsHeaders `
    -Pattern '^Set-Cookie:.*SameSite=Strict'

Show-Result "Cookie SECURITYLABV2SESSID" $cookiePresent ""
Show-Result "Atributo Secure" $cookieSecure ""
Show-Result "Atributo HttpOnly" $cookieHttpOnly ""
Show-Result "SameSite Strict" $cookieSameSite ""

$allPassed = (
    $composeExitCode -eq 0 -and
    $httpExitCode -eq 0 -and
    $httpsExitCode -eq 0 -and
    $httpStatusOk -and
    $locationOk -and
    $httpsStatusOk -and
    $headersPassed -and
    $cookiePresent -and
    $cookieSecure -and
    $cookieHttpOnly -and
    $cookieSameSite
)

Write-Host ""
Write-Host "============================================="

if ($allPassed) {
    Write-Host "VALIDACION HTTPS CORRECTA" -ForegroundColor Green
    Write-Host "V1: http://localhost:8081"
    Write-Host "V2: https://localhost:8443"
    exit 0
}

Write-Host "VALIDACION INCOMPLETA" -ForegroundColor Yellow
Write-Host "Revisa los controles marcados como FAIL."
Write-Host ""
Write-Host "Cabeceras recibidas desde V2:"
Write-Host $httpsHeaders
exit 1