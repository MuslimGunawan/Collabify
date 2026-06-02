# Script to launch all required development servers in Windows Terminal tabs
$scriptPath = $PSScriptRoot
wt -d "$scriptPath" powershell -NoExit -Command "php artisan serve --host=0.0.0.0 --port=8000" `; new-tab -d "$scriptPath" powershell -NoExit -Command "php artisan reverb:start --host=0.0.0.0 --port=8080" `; new-tab -d "$scriptPath" powershell -NoExit -Command "npm run dev"
