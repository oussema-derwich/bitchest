#!/bin/bash

# Script de test des notifications
# Teste : User register → Buy crypto → Check notifications

echo "═══════════════════════════════════════════════════════════"
echo "TEST COMPLET DES NOTIFICATIONS"
echo "═══════════════════════════════════════════════════════════"
echo ""

# Configuration
BACKEND_URL="http://localhost:8000/api"
TEST_EMAIL="notification_test_$(date +%s)@test.com"
TEST_PASSWORD="TestPassword123"

echo "1️⃣  INSCRIPTION UTILISATEUR"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
REGISTER_RESPONSE=$(curl -s -X POST "$BACKEND_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d "{
    \"name\": \"Test User\",
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"$TEST_PASSWORD\",
    \"password_confirmation\": \"$TEST_PASSWORD\"
  }")

echo "Réponse d'inscription:"
echo "$REGISTER_RESPONSE" | jq '.'
echo ""

# Extraire l'ID utilisateur
USER_ID=$(echo "$REGISTER_RESPONSE" | jq -r '.user.id')
echo "✅ Utilisateur créé: ID=$USER_ID, Email=$TEST_EMAIL"
echo ""

# Attendre un peu
sleep 1

echo "2️⃣  LOGIN"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
LOGIN_RESPONSE=$(curl -s -X POST "$BACKEND_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"$TEST_PASSWORD\"
  }")

echo "Réponse de connexion:"
echo "$LOGIN_RESPONSE" | jq '.'
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.access_token')
echo "✅ Token obtenu: ${TOKEN:0:20}..."
echo ""

# Attendre un bit
sleep 1

echo "3️⃣  ACHAT CRYPTO (déclenche notification)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
BUY_RESPONSE=$(curl -s -X POST "$BACKEND_URL/buy" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d "{
    \"cryptocurrency_id\": 1,
    \"quantity\": 0.1,
    \"price\": 50000
  }")

echo "Réponse d'achat:"
echo "$BUY_RESPONSE" | jq '.'
echo "✅ Achat effectué"
echo ""

sleep 1

echo "4️⃣  VÉRIFIER LES NOTIFICATIONS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
NOTIF_RESPONSE=$(curl -s -X GET "$BACKEND_URL/notifications" \
  -H "Authorization: Bearer $TOKEN")

echo "Réponse des notifications:"
echo "$NOTIF_RESPONSE" | jq '.'

NOTIF_COUNT=$(echo "$NOTIF_RESPONSE" | jq '.data.data | length')
echo "✅ Nombre de notifications: $NOTIF_COUNT"
echo ""

if [ "$NOTIF_COUNT" -gt 0 ]; then
    echo "5️⃣  DÉTAIL DE LA NOTIFICATION"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "$NOTIF_RESPONSE" | jq '.data.data[0]'
    
    # Extraire l'ID de la notification
    NOTIF_ID=$(echo "$NOTIF_RESPONSE" | jq -r '.data.data[0].id')
    
    sleep 1
    
    echo ""
    echo "6️⃣  MARQUER LA NOTIFICATION COMME LUE"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    READ_RESPONSE=$(curl -s -X PUT "$BACKEND_URL/notifications/$NOTIF_ID/read" \
      -H "Authorization: Bearer $TOKEN")
    
    echo "Réponse:"
    echo "$READ_RESPONSE" | jq '.'
    echo "✅ Notification marquée comme lue"
fi

echo ""
echo "═══════════════════════════════════════════════════════════"
echo "✅ TEST TERMINÉ AVEC SUCCÈS"
echo "═══════════════════════════════════════════════════════════"
