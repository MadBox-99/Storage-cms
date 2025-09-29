# Egyszerű telepítési stratégia - Kis cégeknek

## Célcsoport
- **Kis- és középvállalkozások** (5-50 alkalmazott)
- **Egyedi telepítések** (on-premise vagy saját VPS)
- **Egy cég = egy telepítés**

## Architektúra

### 1. **Alap CMS (kötelező)**
```
- User management
- Role & Permission
- Basic settings
- Dashboard
```

### 2. **Warehouse Modul (opcionális)**
```
- Raktárkezelés
- Készletnyilvántartás
- Beszerzés, kiadás
- Leltár
```

### 3. **HR/Contact Modul (opcionális)**
```
- Dolgozók
- Kapcsolattartók
- Szervezet
```

## Miért NEM kell Tenant?

✅ **Egyszerűség**
- Nincs multi-tenant komplexitás
- Könnyebb telepítés és karbantartás
- Kevesebb hiba

✅ **Biztonság**
- Teljes adatszeparáció
- Nincs véletlenül átszivárogtathato adat
- Egyszerűbb GDPR compliance

✅ **Testreszabhatóság**
- Minden ügyfél saját verziót kaphat
- Egyedi fejlesztések lehetségesek
- Nincs konfliktus más bérlőkkel

✅ **Költség**
- Olcsóbb fejlesztés
- Egyszerűbb hosting (akár shared hosting is)
- Kevesebb support

## Telepítési példák

### Kis raktár (5-10 fő)
```yaml
Modulok:
- Alap CMS
- Warehouse modul

Hosting:
- Shared hosting vagy kis VPS
- MySQL database
- 2GB RAM elég
```

### Közepes cég (20-50 fő)
```yaml
Modulok:
- Alap CMS
- Warehouse modul
- HR modul

Hosting:
- Dedikált VPS
- MySQL/PostgreSQL
- 4-8GB RAM
```

## Laravel implementáció

### Egyszerű model struktúra
```php
// Nincs tenant_id, nincs scope, tiszta kód
class Product extends Model {
    protected $fillable = [
        'sku',
        'name',
        'price',
        'stock'
    ];

    // Egyszerű relációk
    public function movements() {
        return $this->hasMany(StockMovement::class);
    }
}
```

### Module config
```php
// config/modules.php
return [
    'warehouse' => env('MODULE_WAREHOUSE', false),
    'hr' => env('MODULE_HR', false),
];

// .env példa kis cégnek
MODULE_WAREHOUSE=true
MODULE_HR=false  # Nem vette meg
```

## Árazási modell

### Egyszeri licensz díj
- **Alap CMS**: €500
- **Warehouse modul**: €300
- **HR modul**: €200
- **Telepítés + oktatás**: €200

### Példa árak
- Kis raktár: €500 + €300 = **€800**
- Teljes csomag: €500 + €300 + €200 = **€1000**

### Support (opcionális)
- €50/hó - email support
- €150/hó - priority support + frissítések

## Előnyök kis cégeknek

1. **Megfizethető** - egyszeri díj, nincs havi költség
2. **Sajat szerver** - teljes kontroll
3. **Egyszerű** - nincs felesleges funkció
4. **Biztonságos** - izolált adatok
5. **Testreszabható** - egyedi igények

## Konklúzió

**Tenant = felesleges komplexitás kis telepítésekhez!**

Fókuszálj:
- Egyszerű, tiszta kódra
- Moduláris felépítésre
- Könnyű telepíthetőségre
- Jó dokumentációra

Ez sokkal jobban eladható kis cégeknek!