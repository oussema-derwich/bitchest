# Commandes Rapides - Configuration et Mapping

## 🚀 Commandes Essentielles

### Voir la Configuration

```bash
cd backend

# Rapport complet en table
php artisan mapping:report

# Rapport en JSON
php artisan mapping:report --format=json
```

### Valider la Configuration

```bash
# Valider toutes les configurations
php artisan mapping:validate

# Affiche les erreurs et avertissements
# Statut: ✅ OK ou ❌ ERREURS
```

### Accéder aux Configurations en PHP

```php
// Via helpers globales (plus simple)
validation_rules('User', 'register');
model_mapping('Wallet');
has_permission('admin', 'manage_users');
transform_model('User', 'private');

// Via le service
use App\Services\MappingService;
MappingService::getModelMapping('User');
MappingService::getValidationRules('User', 'register');
MappingService::hasPermission('admin', 'manage_users');
```

---

## 📋 Liste des Configurations Disponibles

### Modèles (8)
- User
- Wallet
- Cryptocurrency
- Transaction
- Alert
- Notification
- PriceHistory
- WalletCrypto

### Validations
- User: register, login, profile
- Wallet: buy, sell
- Alert: store

### Transformations
- User: public, private
- Wallet: default, detailed
- Cryptocurrency: list, detailed
- Transaction: summary, detailed

### Services (7 à créer)
- AuthService
- WalletService
- CryptoService
- TransactionService
- AlertService
- NotificationService
- PortfolioService

### Événements (6)
- UserRegistered
- UserLoggedIn
- CryptoPriceUpdated
- TransactionCompleted
- AlertTriggered
- WalletUpdated

### Jobs (5)
- UpdateCryptoPrices
- CheckPriceAlerts
- SendNotifications
- CleanupNotifications
- CalculatePortfolioValue

### Observateurs (6)
- UserObserver
- WalletObserver
- TransactionObserver
- AlertObserver
- NotificationObserver
- CryptoObserver

---

## 🔧 Ajouter une Nouvelle Configuration

### 1. Ajouter dans le fichier config
```php
// Dans config/mapping.php ou config/services-config.php
'new_item' => [
    // Configuration...
]
```

### 2. Utiliser dans le code
```php
// Via helpers
$config = model_mapping('NewItem');

// Via service
$config = MappingService::getModelMapping('NewItem');
```

### 3. Valider
```bash
php artisan mapping:validate
```

---

## 📍 Localisation des Fichiers

| Fichier | Chemin |
|---------|--------|
| Config Modèles | `backend/config/mapping.php` |
| Config Services | `backend/config/services-config.php` |
| Service Central | `backend/app/Services/MappingService.php` |
| Helpers | `backend/app/Helpers/MappingHelpers.php` |
| Trait | `backend/app/Traits/UsesMappingConfig.php` |
| Provider | `backend/app/Providers/MappingServiceProvider.php` |
| Commande Report | `backend/app/Console/Commands/MappingReportCommand.php` |
| Commande Validate | `backend/app/Console/Commands/ValidateMappingCommand.php` |

---

## 💡 Exemples d'Utilisation dans le Code

### Dans un Contrôleur

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Utiliser les validations centralisées
        $validated = $request->validate(
            validation_rules('User', 'register')
        );
        
        return User::create($validated);
    }
    
    public function login(Request $request)
    {
        $validated = $request->validate(
            validation_rules('User', 'login')
        );
        
        // ...
    }
}
```

### Dans un Service

```php
namespace App\Services;

use App\Services\MappingService;

class WalletService
{
    public function getRelations()
    {
        return MappingService::getModelRelations('Wallet');
    }
    
    public function transformWallet($wallet)
    {
        $fields = MappingService::getTransformation('Wallet', 'detailed');
        return $wallet->only($fields);
    }
}
```

### Dans un Middleware

```php
namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    public function handle($request, Closure $next)
    {
        if (!has_permission(auth()->user()->role, 'manage_users')) {
            abort(403);
        }
        
        return $next($request);
    }
}
```

### Dans une Requête

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function rules()
    {
        return validation_rules('User', 'register');
    }
}
```

---

## 🔍 Debugging

### Voir une configuration spécifique

```php
// Dans tinker ou un contrôleur
$mapping = model_mapping('User');
dd($mapping);

$rules = validation_rules('User', 'register');
dd($rules);

$perms = has_permission('admin', 'manage_users');
dd($perms);
```

### Voir toutes les configurations

```php
// Dans tinker
$report = mapping_report();
dd($report);
```

### Exécuter les commandes

```bash
# Depuis le backend
php artisan mapping:report
php artisan mapping:validate

# Avec plus de détails
php artisan mapping:report --format=json | jq
```

---

## 📊 État Actuel

**Configuration**: ✅ Complétée (50+ configurations)
**Implementation**: ⏳ À démarrer (21 élements à créer)
**Documentation**: ✅ Complète (4 fichiers + guides)
**Tests**: ✅ Validé (commandes fonctionnelles)

---

## 🎯 Prochaines Actions

1. Créer les 7 services (AuthService, WalletService, etc.)
2. Implémenter les 6 événements
3. Créer les 5 jobs
4. Ajouter les 6 observateurs

Voir `MAPPING_STATUS.md` pour le détail.

---

## 📞 Besoin d'Aide ?

1. Consulter `GUIDE_MAPPINGS.md` pour l'utilisation
2. Voir `CONFIG_MAPPING_README.md` pour la vue d'ensemble
3. Exécuter `php artisan mapping:report` pour voir les configurations
4. Exécuter `php artisan mapping:validate` pour trouver les erreurs

