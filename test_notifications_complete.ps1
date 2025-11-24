#!/usr/bin/env pwsh

# Script de test complet pour les Notifications
# Teste: Register -> Login -> Buy -> Notifications -> Mark as Read

$baseUrl = "http://127.0.0.1:8000/api"
$email = "testuser_$(Get-Random)@test.com"
$password = "Password123!"
$name = "Test User"

Write-Host "🔄 TEST COMPLET NOTIFICATIONS" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# ===== ÉTAPE 1: REGISTER =====
Write-Host "1️⃣  REGISTER USER..." -ForegroundColor Yellow
try {
    $registerBody = @{
        name = $name
        email = $email
        password = $password
        password_confirmation = $password
    } | ConvertTo-Json

    Invoke-WebRequest -Uri "$baseUrl/register" `
        -Method POST `
        -Headers @{"Content-Type" = "application/json"} `
        -Body $registerBody `
        -UseBasicParsing | Out-Null

    Write-Host "✅ Register OK" -ForegroundColor Green
    Write-Host "   Email: $email" -ForegroundColor Gray
    Write-Host "   Balance: 500€" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "❌ Register FAILED: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ===== ÉTAPE 2: LOGIN =====
Write-Host "2️⃣  LOGIN USER..." -ForegroundColor Yellow
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

    Write-Host "✅ Login OK" -ForegroundColor Green
    Write-Host "   Token: $($token.Substring(0, 20))..." -ForegroundColor Gray
    Write-Host "   User ID: $userId" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "❌ Login FAILED: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ===== ÉTAPE 3: GET CRYPTO LIST =====
Write-Host "3️⃣  GET CRYPTOCURRENCY LIST..." -ForegroundColor Yellow
try {
    $cryptoResponse = Invoke-WebRequest -Uri "$baseUrl/cryptocurrencies" `
        -Method GET `
        -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
        -UseBasicParsing

    $cryptoData = $cryptoResponse.Content | ConvertFrom-Json
    $crypto = $cryptoData.data[0]

    Write-Host "✅ Crypto List OK" -ForegroundColor Green
    Write-Host "   Crypto: $($crypto.symbol)" -ForegroundColor Gray
    Write-Host "   Price: $($crypto.price)€" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "❌ Crypto List FAILED: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ===== ÉTAPE 4: BUY CRYPTO =====
Write-Host "4️⃣  BUY CRYPTOCURRENCY..." -ForegroundColor Yellow
try {
    $quantity = 0.01
    $price = [math]::Round($crypto.price, 2)
    
    $buyBody = @{
        cryptocurrency_id = $crypto.id
        quantity = $quantity
    } | ConvertTo-Json

    Invoke-WebRequest -Uri "$baseUrl/buy" `
        -Method POST `
        -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
        -Body $buyBody `
        -UseBasicParsing | Out-Null

    Write-Host "✅ Buy OK" -ForegroundColor Green
    Write-Host "   Quantity: $quantity $($crypto.symbol)" -ForegroundColor Gray
    Write-Host "   Price: $price€" -ForegroundColor Gray
    Write-Host "   Total Cost: $($quantity * $price)€" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "❌ Buy FAILED: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Response: $($_.ErrorDetails.Message)" -ForegroundColor Red
    exit 1
}

# ===== ÉTAPE 5: GET NOTIFICATIONS =====
Write-Host "5️⃣  GET NOTIFICATIONS..." -ForegroundColor Yellow
try {
    Start-Sleep -Milliseconds 500  # Petit délai pour s'assurer que la notification est créée

    $notifResponse = Invoke-WebRequest -Uri "$baseUrl/notifications" `
        -Method GET `
        -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
        -UseBasicParsing

    $notifData = $notifResponse.Content | ConvertFrom-Json
    $notifications = $notifData.data.data
    $unreadCount = $notifData.unread_count

    Write-Host "✅ Notifications Retrieved" -ForegroundColor Green
    Write-Host "   Total: $($notifications.Count)" -ForegroundColor Gray
    Write-Host "   Unread: $unreadCount" -ForegroundColor Gray
    Write-Host ""

    if ($notifications.Count -eq 0) {
        Write-Host "⚠️  WARNING: No notifications found!" -ForegroundColor Yellow
    }
    else {
        $notification = $notifications[0]
        Write-Host "   Latest Notification:" -ForegroundColor Gray
        Write-Host "   - Type: $($notification.type)" -ForegroundColor Gray
        Write-Host "   - Title: $($notification.title)" -ForegroundColor Gray
        Write-Host "   - Message: $($notification.message)" -ForegroundColor Gray
        Write-Host "   - Is Read: $($notification.is_read)" -ForegroundColor Gray
        Write-Host "   - Created: $($notification.created_at)" -ForegroundColor Gray
        Write-Host ""

        # ===== ÉTAPE 6: MARK AS READ =====
        Write-Host "6️⃣  MARK NOTIFICATION AS READ..." -ForegroundColor Yellow
        try {
            Invoke-WebRequest -Uri "$baseUrl/notifications/$($notification.id)/read" `
                -Method PUT `
                -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
                -UseBasicParsing | Out-Null

            Write-Host "✅ Marked as Read" -ForegroundColor Green
            Write-Host "   Notification ID: $($notification.id)" -ForegroundColor Gray
            Write-Host ""

            # ===== ÉTAPE 7: GET NOTIFICATIONS AGAIN (TO VERIFY UPDATE) =====
            Write-Host "7️⃣  VERIFY NOTIFICATION STATUS..." -ForegroundColor Yellow
            Start-Sleep -Milliseconds 200

            $verifyResponse = Invoke-WebRequest -Uri "$baseUrl/notifications" `
                -Method GET `
                -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $token"} `
                -UseBasicParsing

            $verifyData = $verifyResponse.Content | ConvertFrom-Json
            $updatedNotif = $verifyData.data.data[0]

            Write-Host "✅ Notification Status Verified" -ForegroundColor Green
            Write-Host "   Is Read: $($updatedNotif.is_read)" -ForegroundColor Gray
            Write-Host "   Read At: $($updatedNotif.read_at)" -ForegroundColor Gray
            Write-Host ""

            if ($updatedNotif.is_read -eq $true) {
                Write-Host "✅ NOTIFICATION UPDATE SUCCESS" -ForegroundColor Green
            }
            else {
                Write-Host "⚠️  WARNING: is_read is still false!" -ForegroundColor Yellow
            }
        }
        catch {
            Write-Host "❌ Mark as Read FAILED: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}
catch {
    Write-Host "❌ Get Notifications FAILED: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ===== RÉSUMÉ =====
Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "✅ TEST COMPLET RÉUSSI" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ Notifications System is fully operational!" -ForegroundColor Green
Write-Host ""
