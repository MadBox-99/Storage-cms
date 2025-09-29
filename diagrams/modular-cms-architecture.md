# Moduláris CMS Architektúra Terv

## 1. **Alap CMS (Core)**
```
- User management (authentication)
- Role & Permission system
- Basic settings
- Module manager
- API layer
```

## 2. **Opcionális Tenant Modul**
```
- NEM kötelező, de telepíthető
- Ha telepítve van → multi-tenant mód
- Ha nincs → single-tenant mód
```

## 3. **Modulok**

### **Warehouse Module**
```
- Raktárkezelés
- Készletnyilvántartás
- Beszerzés
- Független a Tenant-tól
```

### **HR/Contact Module**
```
- Dolgozók kezelése
- Kapcsolattartók
- Szervezeti struktúra
- Független a Tenant-tól
```

## 4. **Architektúra megvalósítás**

### **Option A: Tenant nélkül (Egyszerűbb)**
```php
// Minden modul közvetlenül kapcsolódik
class Product extends Model {
    // Nincs tenant_id
    protected $fillable = ['name', 'sku', ...];
}
```

**Előnyök:**
- ✅ Egyszerűbb fejlesztés
- ✅ Modulok önállóan értékesíthetők
- ✅ Nincs overhead
- ✅ Könnyebb debug

**Hátrányok:**
- ❌ Minden ügyfélnek külön telepítés kell
- ❌ Nincs központi management
- ❌ Több karbantartás

### **Option B: Opcionális Tenant (Rugalmas)**
```php
// Trait használata
trait TenantAware {
    public static function bootTenantAware() {
        if (config('app.multi_tenant')) {
            static::addGlobalScope(new TenantScope);
        }
    }
}

class Product extends Model {
    use TenantAware; // Csak ha kell

    protected $fillable = ['tenant_id', 'name', 'sku', ...];
}
```

**Előnyök:**
- ✅ Rugalmas - lehet single és multi-tenant is
- ✅ Egy kódbázis
- ✅ Skálázható

**Hátrányok:**
- ❌ Komplexebb kód
- ❌ Több tesztelés kell

### **Option C: Teljes Tenant (SaaS)**
```php
// Minden model tenant-aware
abstract class BaseModel extends Model {
    protected static function booted() {
        static::addGlobalScope(new TenantScope);
    }
}
```

**Előnyök:**
- ✅ Igazi SaaS
- ✅ Központi management
- ✅ Egy deployment

**Hátrányok:**
- ❌ Minden modul függeni fog
- ❌ Nem értékesíthető külön
- ❌ Komplexebb

## 5. **Javaslat: Option B - Opcionális Tenant**

### **Miért?**
1. **Flexibilitás** - lehet egyedi telepítés VAGY SaaS
2. **Moduláris** - modulok működnek tenant nélkül is
3. **Jövőbiztos** - később könnyen SaaS-sá alakítható

### **Implementáció Laravel-ben:**

```php
// config/modules.php
return [
    'multi_tenant' => env('ENABLE_MULTI_TENANT', false),
    'modules' => [
        'warehouse' => true,
        'hr' => true,
    ]
];

// app/Models/Traits/TenantAware.php
trait TenantAware {
    public static function bootTenantAware() {
        if (config('modules.multi_tenant')) {
            static::creating(function ($model) {
                if (auth()->check()) {
                    $model->tenant_id = auth()->user()->tenant_id;
                }
            });

            static::addGlobalScope('tenant', function ($query) {
                if (auth()->check()) {
                    $query->where('tenant_id', auth()->user()->tenant_id);
                }
            });
        }
    }
}

// Warehouse Module Model
class Product extends Model {
    use TenantAware; // Automatikus, ha van tenant
}

// HR Module Model
class Employee extends Model {
    use TenantAware; // Automatikus, ha van tenant
}
```

### **Database Migrations:**
```php
// Ha multi-tenant mód
if (config('modules.multi_tenant')) {
    $table->foreignId('tenant_id')->constrained();
    $table->index('tenant_id');
}
```

## 6. **Deployment stratégiák**

### **Single-tenant deployment:**
```yaml
# .env
ENABLE_MULTI_TENANT=false
MODULE_WAREHOUSE=true
MODULE_HR=false  # Nem vásárolta meg
```

### **Multi-tenant SaaS deployment:**
```yaml
# .env
ENABLE_MULTI_TENANT=true
MODULE_WAREHOUSE=true
MODULE_HR=true
```

## 7. **Értékesítési modellek**

1. **Alap CMS** - €500
2. **Warehouse Module** - €300
3. **HR Module** - €200
4. **Multi-tenant addon** - €500
5. **Full SaaS package** - €99/hó/tenant

## 8. **Konklúzió**

**Kezdj Option A-val (tenant nélkül)**, mert:
- Gyorsabban piacra vihető
- Egyszerűbb support
- Modulok önállóan értékesíthetők

**Később áttérhetsz Option B-re**, amikor:
- Van 5+ ügyfeled
- Igény van SaaS-ra
- Van kapacitás a komplexitásra

A Tenant NEM kötelező a sikerhez, de jó ha a kód felkészül rá!