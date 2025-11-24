# Guide de Configuration des Mappings

## Vue d'ensemble

Le système de mappings centralise toutes les configurations de votre application. Cela inclut:
- Les relations entre modèles
- Les règles de validation
- Les transformations de données
- Les services et événements
- Les jobs planifiés
- Les permissions et rôles

## Architecture

### 1. **config/mapping.php** - Configuration des Modèles
Définit:
- Les relations entre modèles (hasMany, belongsTo, etc.)
- Les attributs fillable et hidden
- Les castages de types
- Les règles de validation
- Les transformations de données

### 2. **config/services-config.php** - Configuration des Services
Définit:
- Les services applicatifs
- Les événements et leurs listeners
- Les jobs planifiés
- Les observateurs de modèles
- La configuration du cache
- Les permissions par rôle
- Les APIs externes

### 3. **app/Services/MappingService.php** - Service Central
Fournit les méthodes pour accéder à toutes les configurations:
```php
MappingService::getModelMapping('User');
MappingService::getValidationRules('User', 'register');
MappingService::getPermissions('admin');
```

### 4. **app/Helpers/MappingHelpers.php** - Fonctions d'Aide
Fournit des fonctions globales:
```php
model_mapping('User');
validation_rules('User', 'login');
transform_model('User', 'private');
has_permission('admin', 'manage_users');
```

### 5. **app/Traits/UsesMappingConfig.php** - Trait pour Modèles
Permet aux modèles d'utiliser les configurations:
```php
class User extends Model {
    use UsesMappingConfig;
}
```

## Utilisation

### Accéder aux Configurations

#### Via le Service
```php
use App\Services\MappingService;

// Obtenir le mapping d'un modèle
$userMapping = MappingService::getModelMapping('User');

// Obtenir les relations
$relations = MappingService::getModelRelations('Wallet');

// Obtenir les règles de validation
$rules = MappingService::getValidationRules('User', 'register');

// Vérifier une permission
$canDelete = MappingService::hasPermission('admin', 'manage_users');
```

#### Via les Helpers
```php
// Accès direct via fonctions globales
$mapping = model_mapping('User');
$rules = validation_rules('Wallet', 'buy');
$can = has_permission('user', 'view_wallet');
$transform = transform_model('Transaction', 'detailed');
```

#### Via les Modèles
```php
use App\Traits\UsesMappingConfig;

class User extends Model {
    use UsesMappingConfig;
    
    public function getConfig() {
        return $this->getModelConfig();
    }
}

// Utilisation
$user = User::first();
$config = $user->getModelConfig();
```

### Appliquer les Validations

```php
// Dans un contrôleur
use App\Services\MappingService;

public function register(Request $request)
{
    $validated = $request->validate(
        MappingService::getValidationRules('User', 'register')
    );
    
    // ...
}
```

### Transformer les Données

```php
// Lors du retour d'une réponse API
use App\Services\MappingService;

public function show(User $user)
{
    $fields = MappingService::getTransformation('User', 'private');
    return $user->only($fields);
}
```

### Vérifier les Permissions

```php
// Dans un middleware ou un service
if (!MappingService::hasPermission(auth()->user()->role, 'manage_users')) {
    abort(403);
}
```

## Structure des Configurations

### Mapping d'un Modèle
```php
'user' => [
    'class' => 'App\Models\User',
    'table' => 'users',
    'relations' => [
        'wallets' => [
            'type' => 'hasMany',
            'model' => 'App\Models\Wallet',
            'foreign_key' => 'user_id',
            'local_key' => 'id',
        ],
    ],
    'attributes' => [
        'fillable' => ['name', 'email', 'password'],
        'hidden' => ['password'],
        'casts' => ['email_verified_at' => 'datetime'],
    ],
]
```

### Mapping d'un Service
```php
'wallet' => [
    'class' => 'App\Services\WalletService',
    'methods' => [
        'create',
        'getBalance',
        'updateBalance',
        'getTransactions',
    ],
]
```

### Mapping d'un Événement
```php
'TransactionCompleted' => [
    'class' => 'App\Events\TransactionCompleted',
    'listeners' => [
        'App\Listeners\UpdateWalletBalance',
        'App\Listeners\NotifyUser',
    ],
]
```

### Mapping d'un Job
```php
'UpdateCryptoPrices' => [
    'class' => 'App\Jobs\UpdateCryptoPrices',
    'schedule' => 'everyMinute',
    'timeout' => 300,
    'description' => 'Met à jour les prix des cryptomonnaies',
]
```

## Avantages

1. **Centralisation** - Toutes les configurations au même endroit
2. **Maintenabilité** - Modifications faciles et cohérentes
3. **Réutilisabilité** - Accès unifié aux configurations
4. **Type-safe** - Les configurations peuvent être validées
5. **Documentation** - Le code est auto-documenté
6. **DRY** - Pas de répétition de code

## Extension

Pour ajouter un nouveau modèle:

1. Ajoutez la configuration dans `config/mapping.php`:
```php
'newmodel' => [
    'class' => 'App\Models\NewModel',
    'table' => 'new_models',
    'relations' => [...],
    'attributes' => [...],
]
```

2. Utilisez `MappingService::getModelMapping('NewModel')` pour y accéder

## Performance

Le cache est utilisé pour les données fréquemment accédées:
```php
// Configuration du cache
'caches' => [
    'cryptocurrencies' => [
        'key' => 'crypto:all',
        'ttl' => 300, // 5 minutes
    ],
]
```

## Debugging

Pour voir toutes les configurations:
```php
// Commande artisan (à créer)
php artisan mapping:report

// Ou via code
$report = MappingService::generateMappingReport();
dd($report);
```

## Points d'Entrée Principaux

- **Accès service**: `app('mapping')` ou `MappingService::`
- **Accès helper**: Fonctions globales comme `model_mapping()`, `validation_rules()`, etc.
- **Accès modèle**: Via le trait `UsesMappingConfig`
- **Registration**: `MappingServiceProvider` dans `bootstrap/providers.php`

## Migrer du Code Existant

Si vous avez du code existant utilisant des configurations en dur:

```php
// Avant
class UserController {
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);
    }
}

// Après
class UserController {
    public function store(Request $request) {
        $request->validate(validation_rules('User', 'register'));
    }
}
```

