# Configuration et Mapping Centralisés

## 📋 Sommaire

Cette documentation couvre le système de configuration et de mapping centralisé qui centralise toutes les configurations de l'application BitchEst.

## 🏗️ Architecture

### Fichiers Clés

1. **`config/mapping.php`** - Configuration des modèles et relations
2. **`config/services-config.php`** - Configuration des services, événements, jobs
3. **`app/Services/MappingService.php`** - Service central d'accès aux configurations
4. **`app/Helpers/MappingHelpers.php`** - Fonctions d'aide globales
5. **`app/Traits/UsesMappingConfig.php`** - Trait pour les modèles
6. **`app/Providers/MappingServiceProvider.php`** - Fournisseur de services

### Points d'Entrée

| Méthode | Usage |
|---------|-------|
| `MappingService::` | Accès via service |
| Fonctions globales | `model_mapping()`, `validation_rules()`, etc. |
| Modèles | Via trait `UsesMappingConfig` |

## 🚀 Démarrage Rapide

### Installation

1. Les fichiers de configuration sont déjà en place
2. Le `MappingServiceProvider` est enregistré dans `bootstrap/providers.php`
3. Les helpers sont auto-chargés via `composer.json`

### Utilisation Basique

```php
// Accéder aux validations
$rules = validation_rules('User', 'register');

// Accéder aux mappings
$mapping = model_mapping('Wallet');

// Vérifier les permissions
$can = has_permission('admin', 'manage_users');
```

## 📚 Documentation Détaillée

Voir le fichier `GUIDE_MAPPINGS.md` pour:
- Guide complet d'utilisation
- Structure détaillée des configurations
- Exemples d'implémentation
- Bonnes pratiques

## 🔧 Commandes Disponibles

### Rapport de Mapping

```bash
# Afficher le rapport en table
php artisan mapping:report

# Afficher le rapport en JSON
php artisan mapping:report --format=json
```

## 📝 Modèles Configurés

- ✅ User
- ✅ Wallet
- ✅ Cryptocurrency
- ✅ Transaction
- ✅ Alert
- ✅ Notification
- ✅ PriceHistory
- ✅ WalletCrypto

## 🎯 Services Configurés

- ✅ AuthService
- ✅ WalletService
- ✅ CryptoService
- ✅ TransactionService
- ✅ AlertService
- ✅ NotificationService
- ✅ PortfolioService

## 📡 Événements Configurés

- UserRegistered → SendWelcomeEmail, CreateDefaultWallet
- UserLoggedIn → UpdateLastLogin, LogLoginActivity
- CryptoPriceUpdated → CheckAlerts, RecordPriceHistory
- TransactionCompleted → UpdateWalletBalance, NotifyUser, RecordTransaction
- AlertTriggered → NotifyUserAlert, CreateNotification
- WalletUpdated → NotifyWalletChange

## ⏱️ Jobs Planifiés

| Job | Schedule | Timeout |
|-----|----------|---------|
| UpdateCryptoPrices | Chaque minute | 300s |
| CheckPriceAlerts | Toutes les 2 min | 60s |
| SendNotifications | Toutes les 5 min | 120s |
| CleanupNotifications | Quotidien | 600s |
| CalculatePortfolioValue | Horaire | 300s |

## 🔐 Permissions par Rôle

### User
- view_own_profile ✅
- edit_own_profile ✅
- view_wallet ✅
- manage_wallet ✅
- view_transactions ✅
- create_transaction ✅
- manage_alerts ✅
- view_notifications ✅

### Admin
- view_all_users ✅
- manage_users ✅
- view_all_transactions ✅
- manage_system ✅
- view_analytics ✅
- manage_cryptocurrencies ✅

## 💾 Cache Configuré

| Type | TTL | Key |
|------|-----|-----|
| Cryptocurrencies | 5 min | `crypto:all` |
| Crypto Price | 1 min | `crypto:price:{id}` |
| User Portfolio | 2 min | `portfolio:{user_id}` |
| Wallet Balance | 1 min | `wallet:{wallet_id}` |

## 🔗 Relations Élément Clé

### User
- hasMany: Wallets, Transactions, Alerts, Notifications

### Wallet
- belongsTo: User
- belongsToMany: Cryptocurrencies (via wallet_cryptocurrencies)
- hasMany: Transactions

### Cryptocurrency
- hasMany: PriceHistories
- belongsToMany: Wallets

### Transaction
- belongsTo: User, Wallet, Cryptocurrency

### Alert
- belongsTo: User, Cryptocurrency

### Notification
- belongsTo: User

## ⚙️ Configuration des APIs Externes

### CoinGecko
- Base URL: `https://api.coingecko.com/api/v3`
- Endpoints: markets, price, chart
- Timeout: 30s

### CoinMarketCap
- Base URL: `https://pro-api.coinmarketcap.com/v1`
- API Key: `env('COINMARKETCAP_API_KEY')`
- Timeout: 30s

## ✨ Avantages du Système

1. **Centralisation** - Une seule source de vérité
2. **Maintenabilité** - Modifications faciles et cohérentes
3. **Réutilisabilité** - Accès unifié aux configurations
4. **Documentation** - Auto-documenté
5. **Performance** - Cache intégré
6. **Extensibilité** - Facile à étendre

## 🐛 Debugging

### Voir toutes les configurations
```php
$report = MappingService::generateMappingReport();
dd($report);
```

### Accéder à une configuration spécifique
```php
$userValidation = MappingService::getValidationRules('User', 'register');
dd($userValidation);
```

### Utiliser la commande de rapport
```bash
php artisan mapping:report
```

## 📖 Prochaines Étapes

1. Appliquer les configurations à vos contrôleurs
2. Utiliser les validations centralisées
3. Implémenter les services configurés
4. Créer les listeners d'événements
5. Planifier les jobs

## 📞 Support

Pour des questions ou des améliorations:
1. Consultez le `GUIDE_MAPPINGS.md`
2. Exécutez `php artisan mapping:report`
3. Vérifiez les fichiers de configuration

## 🔄 Mise à Jour

Pour ajouter une nouvelle configuration:

1. Modifiez le fichier de configuration approprié
2. Accédez via `MappingService::` ou helpers
3. Exécutez `php artisan mapping:report` pour vérifier

## ✅ Checklist de Configuration

- [x] Fichiers de configuration créés
- [x] Service de mapping implémenté
- [x] Helpers globaux enregistrés
- [x] Provider enregistré dans bootstrap
- [x] Trait pour modèles créé
- [x] Commande artisan créée
- [x] Documentation complète

