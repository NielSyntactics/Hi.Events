param(
    [string]$CertsFlag = ""
)

$COMPOSE_CMD = "docker compose -f docker-compose.dev.yml"
$CERTS_DIR = "./certs"

Write-Host "Installing Hi.Events..." -ForegroundColor Green

# Create certs directory
if (-not (Test-Path $CERTS_DIR)) {
    New-Item -ItemType Directory -Path $CERTS_DIR | Out-Null
}

function Generate-UnsignedCerts {
    $certPath = "$CERTS_DIR/localhost.crt"
    $keyPath = "$CERTS_DIR/localhost.key"

    if (-not (Test-Path $certPath) -or -not (Test-Path $keyPath)) {
        Write-Host "Generating unsigned SSL certificates..." -ForegroundColor Green
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout $keyPath -out $certPath -subj "/CN=localhost"
    } else {
        Write-Host "SSL certificates already exist, skipping generation..." -ForegroundColor Green
    }
}

function Generate-SignedCerts {
    $certPath = "$CERTS_DIR/localhost.crt"
    $keyPath = "$CERTS_DIR/localhost.key"

    if (-not (Test-Path $certPath) -or -not (Test-Path $keyPath)) {
        $mkcertCmd = Get-Command mkcert -ErrorAction SilentlyContinue
        if (-not $mkcertCmd) {
            Write-Host "mkcert is not installed." -ForegroundColor Red
            Write-Host "Please install mkcert by following the instructions at: https://github.com/FiloSottile/mkcert#installation"
            Write-Host "Alternatively, you can generate unsigned certificates by omitting the --certs parameter."
            exit 1
        } else {
            Write-Host "Generating signed SSL certificates with mkcert..." -ForegroundColor Green
            mkcert -key-file $keyPath -cert-file $certPath localhost 127.0.0.1 ::1
        }
    } else {
        Write-Host "SSL certificates already exist, skipping generation..." -ForegroundColor Green
    }
}

if ($CertsFlag -eq "--certs=signed") {
    Generate-SignedCerts
} else {
    Generate-UnsignedCerts
}

Write-Host "Starting Docker containers (backend services only)..." -ForegroundColor Green
# Don't start frontend in Docker - it runs locally for development
Invoke-Expression "$COMPOSE_CMD up -d --scale frontend=0"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Failed to start services with docker-compose." -ForegroundColor Red
    exit 1
}

# Stop frontend container if it's running from a previous start
Write-Host "Ensuring frontend container is stopped..." -ForegroundColor Green
Invoke-Expression "$COMPOSE_CMD stop frontend" 2>$null
Invoke-Expression "$COMPOSE_CMD rm -f frontend" 2>$null

Write-Host "Running composer install in the backend service..." -ForegroundColor Green
Invoke-Expression "$COMPOSE_CMD exec -T backend composer install --ignore-platform-reqs --no-interaction --optimize-autoloader --prefer-dist"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Composer install failed within the backend service." -ForegroundColor Red
    exit 1
}

Write-Host "Waiting for the database to be ready..." -ForegroundColor Green
$dbReady = $false
$attempts = 0
$maxAttempts = 120  # 2 minutes timeout

while (-not $dbReady -and $attempts -lt $maxAttempts) {
    $logs = Invoke-Expression "$COMPOSE_CMD logs pgsql 2>&1" | Select-String "ready to accept connections"
    if ($logs) {
        $dbReady = $true
    } else {
        Write-Host -NoNewline "."
        Start-Sleep -Seconds 1
        $attempts++
    }
}

if (-not $dbReady) {
    Write-Host "`nDatabase failed to become ready." -ForegroundColor Red
    exit 1
}

Write-Host "`nDatabase is ready. Proceeding with migrations..." -ForegroundColor Green

# Check and copy .env files
$backendEnv = "$PSScriptRoot/../../backend/.env"
$frontendEnv = "$PSScriptRoot/../../frontend/.env"

if (-not (Test-Path $backendEnv)) {
    Write-Host "Copying backend .env file..." -ForegroundColor Green
    Invoke-Expression "$COMPOSE_CMD exec -T backend cp .env.example .env"
}

if (-not (Test-Path $frontendEnv)) {
    Write-Host "Copying frontend .env file..." -ForegroundColor Green
    Invoke-Expression "$COMPOSE_CMD exec -T frontend cp .env.example .env"
}

Write-Host "Running Laravel setup commands..." -ForegroundColor Green
Invoke-Expression "$COMPOSE_CMD exec -T backend php artisan key:generate"
Invoke-Expression "$COMPOSE_CMD exec -T backend php artisan migrate"
Invoke-Expression "$COMPOSE_CMD exec -T backend chmod -R 775 /var/www/html/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer"
Invoke-Expression "$COMPOSE_CMD exec -T backend php artisan storage:link"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Migrations failed." -ForegroundColor Red
    exit 1
}

Write-Host "Hi.Events is now running at: https://localhost:8443" -ForegroundColor Green

# Open browser
$osType = if ($IsWindows) { "Windows" } elseif ($IsMacOS) { "Darwin" } else { "Linux" }
if ($osType -eq "Windows") {
    Start-Process "https://localhost:8443/auth/register"
} elseif ($osType -eq "Darwin") {
    & open "https://localhost:8443/auth/register"
} else {
    & xdg-open "https://localhost:8443/auth/register"
}
