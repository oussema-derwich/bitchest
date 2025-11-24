# Script de test des notifications (PowerShell)
# Teste : User register → Buy crypto → Check notifications

Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "TEST COMPLET DES NOTIFICATIONS" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$BACKEND_URL = "http://localhost:8000/api"
$TEST_EMAIL = "notif_test_$(Get-Random)@test.com"
$TEST_PASSWORD = "TestPassword123"

Write-Host "1️⃣  INSCRIPTION UTILISATEUR" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$registerBody = @{
    name = "Test User"
    email = $TEST_EMAIL
    password = $TEST_PASSWORD
    password_confirmation = $TEST_PASSWORD
} | ConvertTo-Json

$registerResponse = Invoke-RestMethod -Uri "$BACKEND_URL/auth/register" `
    -Method Post `
    -Headers @{"Content-Type"="application/json"} `
    -Body $registerBody

Write-Host "Réponse d'inscription:"
$registerResponse | ConvertTo-Json | Write-Host
Write-Host ""

$USER_ID = $registerResponse.user.id
Write-Host "✅ Utilisateur créé: ID=$USER_ID, Email=$TEST_EMAIL" -ForegroundColor Green
Write-Host ""

Start-Sleep -Seconds 1

Write-Host "2️⃣  LOGIN" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$loginBody = @{
    email = $TEST_EMAIL
    password = $TEST_PASSWORD
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri "$BACKEND_URL/auth/login" `
    -Method Post `
    -Headers @{"Content-Type"="application/json"} `
    -Body $loginBody

Write-Host "Réponse de connexion:"
$loginResponse | ConvertTo-Json | Write-Host

$TOKEN = $loginResponse.access_token
Write-Host "✅ Token obtenu: $($TOKEN.Substring(0, 20))..." -ForegroundColor Green
Write-Host ""

Start-Sleep -Seconds 1

Write-Host "3️⃣  ACHAT CRYPTO (déclenche notification)" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$buyBody = @{
    cryptocurrency_id = 1
    quantity = 0.1
    price = 50000
} | ConvertTo-Json

$buyResponse = Invoke-RestMethod -Uri "$BACKEND_URL/buy" `
    -Method Post `
    -Headers @{
        "Content-Type" = "application/json"
        "Authorization" = "Bearer $TOKEN"
    } `
    -Body $buyBody

Write-Host "Réponse d'achat:"
$buyResponse | ConvertTo-Json | Write-Host
Write-Host "✅ Achat effectué" -ForegroundColor Green
Write-Host ""

Start-Sleep -Seconds 1

Write-Host "4️⃣  VÉRIFIER LES NOTIFICATIONS" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$notifResponse = Invoke-RestMethod -Uri "$BACKEND_URL/notifications" `
    -Method Get `
    -Headers @{
        "Authorization" = "Bearer $TOKEN"
    }

Write-Host "Réponse des notifications:"
$notifResponse | ConvertTo-Json | Write-Host

$NOTIF_COUNT = $notifResponse.data.data.Count
Write-Host "✅ Nombre de notifications: $NOTIF_COUNT" -ForegroundColor Green
Write-Host ""

if ($NOTIF_COUNT -gt 0) {
    Write-Host "5️⃣  DÉTAIL DE LA NOTIFICATION" -ForegroundColor Green
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
    $notifResponse.data.data[0] | ConvertTo-Json | Write-Host
    
    $NOTIF_ID = $notifResponse.data.data[0].id
    
    Start-Sleep -Seconds 1
    
    Write-Host ""
    Write-Host "6️⃣  MARQUER LA NOTIFICATION COMME LUE" -ForegroundColor Green
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
    
    $readResponse = Invoke-RestMethod -Uri "$BACKEND_URL/notifications/$NOTIF_ID/read" `
        -Method Put `
        -Headers @{
            "Authorization" = "Bearer $TOKEN"
        }
    
    Write-Host "Réponse:"
    $readResponse | ConvertTo-Json | Write-Host
    Write-Host "✅ Notification marquée comme lue" -ForegroundColor Green
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "✅ TEST TERMINÉ AVEC SUCCÈS" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
