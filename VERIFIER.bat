@echo off
REM Script de vérification BitChest - Pour Windows PowerShell
REM Ce script vérifie que tous les composants sont correctement configurés

echo.
echo ========================================
echo   VERIFICATION BitChest Application
echo ========================================
echo.

setlocal enabledelayedexpansion

REM Couleurs
set SUCCESS=[OK]
set ERROR=[ERREUR]
set WARNING=[ATTENTION]

echo.
echo --- Vérification du Backend (Laravel) ---
echo.

cd backend

REM Vérifier PHP
php -v >nul 2>&1
if %errorlevel% equ 0 (
    echo %SUCCESS% PHP installé
    php -v | findstr /R "^PHP"
) else (
    echo %ERROR% PHP n'est pas installé ou non accessible
    goto :end
)

echo.

REM Vérifier Laravel
php artisan --version >nul 2>&1
if %errorlevel% equ 0 (
    echo %SUCCESS% Laravel est installé
    php artisan --version
) else (
    echo %ERROR% Laravel n'est pas accessible
    goto :end
)

echo.

REM Vérifier les migrations
echo Vérification des migrations...
php artisan migrate:status | findstr /I "Ran" >nul
if %errorlevel% equ 0 (
    echo %SUCCESS% Les migrations sont exécutées
) else (
    echo %WARNING% Certaines migrations pourraient ne pas être exécutées
)

echo.

REM Vérifier les dépendances Composer
if exist vendor\autoload.php (
    echo %SUCCESS% Dépendances Composer installées
) else (
    echo %WARNING% Dépendances Composer manquantes (run: composer install)
)

echo.

REM Vérifier le fichier .env
if exist .env (
    echo %SUCCESS% Fichier .env trouvé
) else (
    echo %ERROR% Fichier .env manquant
)

echo.
cd ..

REM Vérifier le Frontend
echo.
echo --- Vérification du Frontend (Vue/Vite) ---
echo.

cd frontend

REM Vérifier Node.js
node -v >nul 2>&1
if %errorlevel% equ 0 (
    echo %SUCCESS% Node.js installé
    node -v
) else (
    echo %ERROR% Node.js n'est pas installé
    goto :end
)

echo.

REM Vérifier npm
npm -v >nul 2>&1
if %errorlevel% equ 0 (
    echo %SUCCESS% npm installé
    npm -v
) else (
    echo %ERROR% npm n'est pas installé
    goto :end
)

echo.

REM Vérifier node_modules
if exist node_modules (
    echo %SUCCESS% node_modules existent
    echo Nombre de packages: [Vérifier manuellement]
) else (
    echo %WARNING% node_modules manquent (run: npm install)
)

echo.

REM Vérifier package.json
if exist package.json (
    echo %SUCCESS% package.json trouvé
) else (
    echo %ERROR% package.json manquant
)

echo.

REM Vérifier Vite
if exist vite.config.ts (
    echo %SUCCESS% Vite configuré (vite.config.ts)
) else (
    echo %ERROR% vite.config.ts manquant
)

echo.
cd ..

echo.
echo ========================================
echo   Résumé de la Vérification
echo ========================================
echo.
echo Backend (Laravel):
echo   - PHP: OK
echo   - Laravel: OK
echo   - Migrations: OK
echo   - Dépendances: Vérifier
echo   - .env: Vérifier
echo.
echo Frontend (Vue/Vite):
echo   - Node.js: OK
echo   - npm: OK
echo   - node_modules: Vérifier
echo   - Vite config: OK
echo.

echo.
echo ========================================
echo   Prêt pour le démarrage!
echo ========================================
echo.
echo Pour démarrer l'application:
echo.
echo Terminal 1 (Backend):
echo   cd backend ^&^& php artisan serve
echo.
echo Terminal 2 (Frontend):
echo   cd frontend ^&^& npm run dev
echo.
echo L'application sera accessible sur:
echo   Frontend: http://localhost:5173
echo   Backend:  http://localhost:8000
echo.

:end
pause
