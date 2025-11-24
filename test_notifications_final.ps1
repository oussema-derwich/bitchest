#!/usr/bin/env pwsh
# -encoding UTF-8

# Script de test complet pour les Notifications (version sans emojis)
# Teste: Register -> Login -> Buy -> Notifications -> Mark as Read

$baseUrl = "http://127.0.0.1:8000/api"
$email = "testuser_$(Get-Random)@test.com"
$password = "Password123!"
$name = "Test User"

Write-Host "[TEST] Notifications System - Complete Validation" -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan
Write-Host ""

# ===== ETAPE 1: REGISTER =====
Write-Host "[1] REGISTER USER..." -ForegroundColor Yellow
try {
    $registerBody = @{
        name = $name
        email = $email
        password = $password
        password_confirmation = $password
    } | ConvertTo-Json

    $registerResponse = Invoke-WebRequest -Uri "$baseUrl/register" `
        -Method POST `
        -Headers @{"Content-Type" = "application/json"} `
        -Body $registerBody `
        -UseBasicParsing

    $registerResponse.Content | ConvertFrom-Json | Out-Null
    Write-Host "[OK] Register Success" -ForegroundColor Green
    Write-Host "     Email: $email" -ForegroundColor Gray
    Write-Host "     Balance: 500 EUR" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "[FAIL] Register Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ===== ETAPE 2: LOGIN =====
Write-Host "[2] LOGIN USER..." -ForegroundColor Yellow
try {
    $loginBody = @{
        email = $email
        password = $password
    } | ConvertTo-Json

    $loginResponse = Invoke-WebRequest -Uri "$baseUrl/login" `
        -Method POST `
        -Headers @{"Content-Type" = "application/json"} `
        -Body $loginBody `
        -UseBasicParsing

    $loginData = $loginResponse.Content | ConvertFrom-Json
    $token = $loginData.token
    $userId = $loginData.user.id

    Write-Host "[OK] Login Success" -ForegroundColor Green
    Write-Host "     Token: $($token.Substring(0, 20))..." -ForegroundColor Gray
    Write-Host "     User ID: $userId" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "[FAIL] Login Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ===== ETAPE 3: GET CRYPTO LIST =====
Write-Host "[3] GET CRYPTOCURRENCY LIST..." -ForegroundColor Yellow
try {
    $cryptoResponse = Invoke-WebRequest -Uri "$baseUrl/cryptocurrencies" `
        -Method GET `
        -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
        -UseBasicParsing

    $cryptoData = $cryptoResponse.Content | ConvertFrom-Json
    $crypto = $cryptoData.data[0]

    Write-Host "[OK] Crypto List Retrieved" -ForegroundColor Green
    Write-Host "     Crypto: $($crypto.symbol)" -ForegroundColor Gray
    Write-Host "     Price: $($crypto.price) EUR" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "[FAIL] Crypto List Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ===== ETAPE 4: BUY CRYPTO =====
Write-Host "[4] BUY CRYPTOCURRENCY..." -ForegroundColor Yellow
try {
    $quantity = 0.01
    $price = [math]::Round($crypto.price, 2)
    
    $buyBody = @{
        cryptocurrency_id = $crypto.id
        quantity = $quantity
    } | ConvertTo-Json

    $buyResponse = Invoke-WebRequest -Uri "$baseUrl/buy" `
        -Method POST `
        -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
        -Body $buyBody `
        -UseBasicParsing

    $buyResponse.Content | ConvertFrom-Json | Out-Null

    Write-Host "[OK] Purchase Successful" -ForegroundColor Green
    Write-Host "     Quantity: $quantity $($crypto.symbol)" -ForegroundColor Gray
    Write-Host "     Price: $price EUR" -ForegroundColor Gray
    Write-Host "     Total: $([math]::Round($quantity * $price, 2)) EUR" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "[FAIL] Buy Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Response: $($_.ErrorDetails.Message)" -ForegroundColor Red
    exit 1
}

# ===== ETAPE 5: GET NOTIFICATIONS =====
Write-Host "[5] GET NOTIFICATIONS..." -ForegroundColor Yellow
try {
    Start-Sleep -Milliseconds 500  # Delay to ensure notification is created

    $notifResponse = Invoke-WebRequest -Uri "$baseUrl/notifications" `
        -Method GET `
        -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
        -UseBasicParsing

    $notifData = $notifResponse.Content | ConvertFrom-Json
    $notifications = $notifData.data.data
    $unreadCount = $notifData.unread_count

    Write-Host "[OK] Notifications Retrieved" -ForegroundColor Green
    Write-Host "     Total: $($notifications.Count)" -ForegroundColor Gray
    Write-Host "     Unread: $unreadCount" -ForegroundColor Gray
    Write-Host ""

    if ($notifications.Count -eq 0) {
        Write-Host "[WARNING] No notifications found!" -ForegroundColor Yellow
        exit 1
    }
    else {
        $notification = $notifications[0]
        Write-Host "[INFO] Latest Notification:" -ForegroundColor Gray
        Write-Host "       Type: $($notification.type)" -ForegroundColor Gray
        Write-Host "       Title: $($notification.title)" -ForegroundColor Gray
        Write-Host "       Message: $($notification.message)" -ForegroundColor Gray
        Write-Host "       Is Read: $($notification.is_read)" -ForegroundColor Gray
        Write-Host "       Created: $($notification.created_at)" -ForegroundColor Gray
        Write-Host ""

        # ===== ETAPE 6: MARK AS READ =====
        Write-Host "[6] MARK NOTIFICATION AS READ..." -ForegroundColor Yellow
        try {
            $markResponse = Invoke-WebRequest -Uri "$baseUrl/notifications/$($notification.id)/read" `
                -Method PUT `
                -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
                -UseBasicParsing

            $markResponse.Content | ConvertFrom-Json | Out-Null

            Write-Host "[OK] Marked as Read" -ForegroundColor Green
            Write-Host "     Notification ID: $($notification.id)" -ForegroundColor Gray
            Write-Host ""

            # ===== ETAPE 7: GET NOTIFICATIONS AGAIN (TO VERIFY UPDATE) =====
            Write-Host "[7] VERIFY NOTIFICATION STATUS..." -ForegroundColor Yellow
            Start-Sleep -Milliseconds 200

            $verifyResponse = Invoke-WebRequest -Uri "$baseUrl/notifications" `
                -Method GET `
                -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
                -UseBasicParsing

            $verifyData = $verifyResponse.Content | ConvertFrom-Json
            $updatedNotif = $verifyData.data.data[0]

            Write-Host "[OK] Notification Status Verified" -ForegroundColor Green
            Write-Host "     Is Read: $($updatedNotif.is_read)" -ForegroundColor Gray
            Write-Host "     Read At: $($updatedNotif.read_at)" -ForegroundColor Gray
            Write-Host ""

            if ($updatedNotif.is_read -eq $true) {
                Write-Host "[SUCCESS] Notification update confirmed!" -ForegroundColor Green
            }
            else {
                Write-Host "[WARNING] is_read is still false!" -ForegroundColor Yellow
                exit 1
            }
        }
        catch {
            Write-Host "[FAIL] Mark as Read Error: $($_.Exception.Message)" -ForegroundColor Red
            exit 1
        }
    }
}
catch {
    Write-Host "[FAIL] Get Notifications Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ===== RESUME =====
Write-Host ""
Write-Host "=================================================" -ForegroundColor Cyan
Write-Host "[FINAL] ALL TESTS PASSED - SYSTEM OPERATIONAL" -ForegroundColor Green
Write-Host "=================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✓ Notifications System is fully functional!" -ForegroundColor Green
Write-Host "✓ Complete flow tested: register -> login -> buy -> notify -> read" -ForegroundColor Green
Write-Host ""
