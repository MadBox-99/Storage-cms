# INTRASTAT Adatszolgáltatás Elkészítési Lépései

## 🎯 Cél
A KSH (Központi Statisztikai Hivatal) felé történő INTRASTAT adatszolgáltatás automatizált elkészítése a rendelések alapján, KSH-Elektra iFORM rendszerrel kompatibilis formátumban.

## ⚠️ Fontos Információk
- **Beküldési rendszer**: KSH-Elektra (https://elektra.ksh.hu)
- **XML formátum**: iFORM szabvány (http://iform-html.kdiv.hu/schemas/form)
- **OSAP kódok**:
  - OSAP 2010: Intrastat Kiszállítás (Dispatch)
  - OSAP 2012: Intrastat Beérkezés (Arrival)
- **Határidő**: Tárgyhónapot követő hónap 12. napja

---

## 📊 1. Adatgyűjtés és Előkészítés

### 1.1 Időszak Meghatározása
- **Referencia év és hónap**: Tárgyhónap (pl. 2025. január)
- **Határidő**: Tárgyhónapot követő hónap 12. napja
- **Példa**: 2025 januári forgalom → beadás: 2025. február 12-ig

### 1.2 Irány Meghatározása
- **ARRIVAL (Érkezés)**: EU-ból Magyarországra történő beszerzések
  - Forrás: `OrderType::PURCHASE` típusú rendelések
  - EU tagállam szállítótól (kivéve HU)

- **DISPATCH (Feladás)**: Magyarországról EU-ba történő értékesítések
  - Forrás: `OrderType::SALES` típusú rendelések
  - EU tagállam vevőhöz (kivéve HU)

### 1.3 Küszöbérték Ellenőrzése
- **Éves küszöb**: 400 millió Ft
- Ha túllépi → kötelező jelenteni
- Külön érkezésre és feladásra

---

## 🔄 2. Automatikus Generálás Folyamata

### 2.1 Service Használata

```php
use App\Services\IntrastatService;
use App\Enums\IntrastatDirection;

$intrastatService = new IntrastatService();

// Deklaráció generálása 2025 januárra - érkezés
$declaration = $intrastatService->generateDeclarationForPeriod(
    year: 2025,
    month: 1,
    direction: IntrastatDirection::ARRIVAL
);
```

### 2.2 Generálási Lépések

**A. Deklaráció Létrehozása**
```
IntrastatDeclaration létrehozása:
- declaration_number: INTRASTAT-202501-A
- direction: ARRIVAL vagy DISPATCH
- reference_year: 2025
- reference_month: 1
- status: DRAFT
```

**B. Releváns Rendelések Kiválasztása**
```sql
SELECT * FROM orders
WHERE type = 'PURCHASE' -- vagy 'SALES'
  AND status IN ('COMPLETED', 'CONFIRMED')
  AND YEAR(order_date) = 2025
  AND MONTH(order_date) = 1
  AND supplier/customer EU tagállam (kivéve HU)
```

**C. Tételek Generálása**
Minden `order_line` alapján `IntrastatLine` létrehozása:

```php
IntrastatLine::create([
    'cn_code' => $product->cn_code,              // 8 jegyű CN kód
    'quantity' => $orderLine->quantity,
    'net_mass' => $product->net_weight * qty,    // kg-ban
    'invoice_value' => $orderLine->line_total,   // HUF
    'statistical_value' => $orderLine->line_total,
    'country_of_origin' => $product->country,
    'country_of_consignment' => $supplier->country,
    'country_of_destination' => $customer->country,
    'transaction_type' => 'OUTRIGHT_PURCHASE_SALE',
    'transport_mode' => 'ROAD',
    'delivery_terms' => 'EXW',
]);
```

**D. Összegzések Számítása**
```php
$declaration->calculateTotals();
// - total_invoice_value
// - total_statistical_value
// - total_net_mass
```

---

## 📝 3. Adatok Ellenőrzése és Validálása

### 3.1 Kötelező Mezők (KSH követelmények)

| Mező | Követelmény | Példa |
|------|-------------|-------|
| **CN kód** | 8 számjegy | 12345678 |
| **Nettó tömeg** | kg, 3 tizedesjegy | 125.500 |
| **Számlaérték** | HUF, egész szám | 500000 |
| **Ország kód** | ISO 3166-1 alpha-2 | DE, AT, SK |
| **Ügylettípus** | 2 számjegy | 11, 21, 31 |
| **Szállítási mód** | 1 számjegy | 3 (közúti) |
| **Szállítási feltétel** | 3 betű Incoterms | EXW, FOB, CIF |

### 3.2 Validációs Szabályok

```php
// CN kód validáció
'cn_code' => 'required|digits:8'

// Ország kódok (csak EU)
'country_of_consignment' => 'required|in:AT,BE,BG,HR,CY,CZ,DK,EE,FI,FR,DE,GR,HU,IE,IT,LV,LT,LU,MT,NL,PL,PT,RO,SK,SI,ES,SE'

// Nettó tömeg minimum 1 gramm
'net_mass' => 'required|numeric|min:0.001'

// Számlaérték minimum 1 HUF
'invoice_value' => 'required|numeric|min:1'
```

---

## 📤 4. XML Export Generálása

### 4.1 iFORM XML Export (KSH-Elektra feltöltéshez)

A hivatalos KSH-Elektra rendszerhez iFORM kompatibilis XML-t kell generálni:

```php
$xml = $intrastatService->exportToIFormXml($declaration);
file_put_contents('intrastat_2025_01.xml', $xml);
```

**iFORM XML Struktúra (Kiszállítás - OSAP 2010):**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<form xmlns="http://iform-html.kdiv.hu/schemas/form">
  <keys>
    <key>
      <name>iformVersion</name>
      <value>1.13.7</value>
    </key>
  </keys>
  <templateKeys>
    <key><name>OSAP</name><value>2010</value></key>
    <key><name>EV</name><value>2025</value></key>
    <key><name>HO</name><value>1</value></key>
    <key><name>VARIANT</name><value>1</value></key>
    <key><name>MUTATION</name><value>0</value></key>
  </templateKeys>
  <chapter s="P">
    <data s="P"><identifier>MHO</identifier><value>01</value></data>
    <data s="P"><identifier>MEV</identifier><value>2025</value></data>
    <data s="P"><identifier>ADOSZAM</identifier><value>12345678-2-42</value></data>
  </chapter>
  <chapter s="P">
    <data s="P"><identifier>LAP_SUM</identifier><value>1</value></data>
    <data s="P"><identifier>LAP_KGM_SUM</identifier><value>20.000</value></data>
    <table name="Termek">
      <row>
        <data s="P"><identifier>T_SORSZ</identifier><value>1</value></data>
        <data s="P"><identifier>TEKOD</identifier><value>84821010</value></data>
        <data s="P"><identifier>RTA</identifier><value>11</value></data>
        <data s="P"><identifier>SZAORSZ</identifier><value>AT</value></data>
        <data s="P"><identifier>KGM</identifier><value>20.000</value></data>
        <data s="P"><identifier>SZAOSSZ</identifier><value>360000</value></data>
        <data s="P"><identifier>SZALMOD</identifier><value>3</value></data>
        <data s="P"><identifier>SZALFEL</identifier><value>FOB</value></data>
      </row>
    </table>
  </chapter>
</form>
```

**Mezők magyarázata (Kiszállítás):**
- `OSAP`: 2010 = Kiszállítás, 2012 = Beérkezés
- `TEKOD`: CN kód (8 jegyű)
- `RTA`: Ügylet jellege (Kiszállítás)
- `FTA`: Ügylet jellege (Beérkezés)
- `SZAORSZ`: Ország kód
- `KGM`: Nettó tömeg (kg, 3 tizedesjegy)
- `SZAOSSZ`: Statisztikai érték (Kiszállítás)
- `STAERT`: Statisztikai érték (Beérkezés)
- `SZALMOD`: Szállítási mód
- `SZALFEL`: Szállítási feltétel
- `SZSZAORSZ`: Származási ország (csak Beérkezésnél)

### 4.2 Egyszerűsített XML Export (Dokumentációhoz)

Belső használatra vagy dokumentációs célra egyszerűsített XML is elérhető:

```php
$xml = $intrastatService->exportToXml($declaration);
```

Ez egy tisztább, olvashatóbb formátumot generál, de **nem kompatibilis** a KSH-Elektra rendszerrel.

---

## ✅ 5. Véglegesítés és Beadás

### 5.1 Státuszok

```php
enum IntrastatStatus: string
{
    case DRAFT = 'DRAFT';           // Munka alatt
    case VALIDATED = 'VALIDATED';   // Ellenőrizve
    case SUBMITTED = 'SUBMITTED';   // Beadva
    case ACCEPTED = 'ACCEPTED';     // Elfogadva
    case REJECTED = 'REJECTED';     // Elutasítva
}
```

### 5.2 Workflow

```
1. DRAFT → Generálás, szerkesztés
2. VALIDATED → Validáció lefutott, hibamentes
3. SUBMITTED → XML feltöltve a KSH rendszerbe
4. ACCEPTED → KSH elfogadta
5. REJECTED → Hibajavítás szükséges → vissza DRAFT-ra
```

### 5.3 Beadási Folyamat (KSH-Elektra)

**1. XML Generálás**
```php
$declaration = $intrastatService->generateDeclarationForPeriod(2025, 1, IntrastatDirection::DISPATCH);
$xml = $intrastatService->exportToIFormXml($declaration);
file_put_contents(storage_path('exports/intrastat_2025_01_dispatch.xml'), $xml);
```

**2. Bejelentkezés a KSH-Elektra rendszerbe**
- URL: https://elektra.ksh.hu/asp/bejelentkezes.html
- Belépés cégkapuval vagy egyéb azonosítóval

**3. XML Feltöltés**
- Adatgyűjtés kiválasztása: OSAP 2010 (Kiszállítás) vagy OSAP 2012 (Beérkezés)
- Időszak megadása (év, hónap)
- XML fájl feltöltése

**4. Validáció**
- A rendszer automatikusan ellenőrzi az XML-t
- Hibák esetén javítás szükséges
- Sikeres validáció esetén: véglegesítés

**5. Véglegesítés és Beküldés**
- Véglegesítés gombra kattintás
- A KSH rendszer visszaigazolást ad
- Státusz frissítése: SUBMITTED

**Alternatív módszer:**
- KSH CSV to XML konverter: https://elektra.ksh.hu/sugo/csv2xml/csv2xml.html
- Először CSV export, majd konvertálás iFORM XML-re

---

## 🔍 6. Ellenőrzési Checklist

### Generálás Előtt
- [ ] Időszak helyesen megadva (év, hónap)
- [ ] Irány kiválasztva (érkezés/feladás)
- [ ] Küszöbérték ellenőrizve

### Generálás Után
- [ ] Minden relevans rendelés feldolgozva
- [ ] CN kódok 8 jegyűek
- [ ] Országkódok EU tagállamok (kivéve HU)
- [ ] Nettó tömeg > 0
- [ ] Számlaérték > 0
- [ ] Összesítések egyeznek

### Export Előtt
- [ ] Adószám helyesen beállítva
- [ ] XML struktúra megfelel a KSH sémának
- [ ] Karakterkódolás UTF-8
- [ ] Dátumok ISO formátumban (YYYY-MM-DD)

### Beadás Után
- [ ] Státusz frissítve
- [ ] Beadás dátuma rögzítve
- [ ] Beadó személy rögzítve
- [ ] Visszaigazolás elmentve

---

## 📈 7. Gyakori Hibák és Megoldások

| Hiba | Ok | Megoldás |
|------|-----|----------|
| **Hiányzó CN kód** | Product táblában nincs kitöltve | Termékadatok kiegészítése |
| **Rossz országkód** | Nem EU vagy HU | Supplier/Customer országkód ellenőrzése |
| **0 nettó tömeg** | Nincs súly megadva | Product net_weight_kg kitöltése |
| **Küszöb alatt** | Túl kevés forgalom | Mentesség a jelentés alól |
| **Duplikált CN kód** | Több tétel ugyanazzal | Összevonás szükséges |

---

## 🛠️ 8. Következő Fejlesztési Lépések

1. **Filament Resource létrehozása** Intrastat kezeléshez
2. **XML Export** implementálása KSH specifikáció alapján
3. **Automatikus validáció** form mentéskor
4. **Bulk actions**: több hónap egy időben
5. **Email értesítés** határidő előtt
6. **Audit log**: ki mit módosított
7. **Preview** funkció XML exporthoz
8. **Import** funkció meglévő XML-ből

---

## 📚 Referenciák

- **KSH Intrastat**: https://www.ksh.hu/intrastat_elektronikus_adatszolgaltatas
- **CN kódok**: https://ec.europa.eu/taxation_customs/dds2/taric/taric_consultation.jsp
- **Incoterms**: https://iccwbo.org/resources-for-business/incoterms-rules/
- **EU tagállamok**: https://europa.eu/european-union/about-eu/countries_en

---

**Utolsó frissítés**: 2025-10-01
**Készítette**: Claude Code
