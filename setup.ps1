# Setup Script for Collabify

Write-Host "==========================================" -ForegroundColor Green
Write-Host "   Setting up Collabify Application       " -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green

# 1. Copy .env if not exists
if (-not (Test-Path ".env")) {
    Write-Host "[1/6] Copying .env.example to .env..." -ForegroundColor Cyan
    Copy-Item ".env.example" ".env"
    Write-Host ".env file created." -ForegroundColor Green
} else {
    Write-Host "[1/6] .env file already exists. Skipping copy." -ForegroundColor Yellow
}

# 2. Run Composer Install
Write-Host "[2/6] Running composer install..." -ForegroundColor Cyan
composer install --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Composer install failed!" -ForegroundColor Red
    exit 1
}

# 3. Run NPM Install
Write-Host "[3/6] Running npm install..." -ForegroundColor Cyan
npm install
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: npm install failed!" -ForegroundColor Red
    exit 1
}

# 4. Generate App Key
Write-Host "[4/6] Generating Application Key..." -ForegroundColor Cyan
php artisan key:generate --no-interaction

# 5. Create MySQL Database if not exists
Write-Host "[5/6] Ensuring MySQL Database 'collabify' exists..." -ForegroundColor Cyan
php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    \$pdo->exec('CREATE DATABASE IF NOT EXISTS collabify');
    echo 'Database \"collabify\" created or already exists successfully.\n';
} catch (Exception \$e) {
    echo 'Warning: Could not connect to MySQL database to create \"collabify\". Please ensure MySQL is running.\n';
    echo 'Details: ' . \$e->getMessage() . '\n';
}
"

# 6. Run Database Migrations
Write-Host "[6/6] Running database migrations..." -ForegroundColor Cyan
php artisan migrate --force --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "Warning: Migrations failed. Please check your MySQL connection and try running 'php artisan migrate' manually." -ForegroundColor Yellow
}

# 7. Compile Assets
Write-Host "Compiling frontend assets for production..." -ForegroundColor Cyan
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: npm run build failed!" -ForegroundColor Red
    exit 1
}

Write-Host "==========================================" -ForegroundColor Green
Write-Host " Setup completed successfully!            " -ForegroundColor Green
Write-Host " Run './start-servers.ps1' to run the app " -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
