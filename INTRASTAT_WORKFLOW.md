# INTRASTAT Adatszolgáltatás Elkészítési Lépései

## 🎯 Cél
A KSH (Központi Statisztikai Hivatal) felé történő INTRASTAT adatszolgáltatás automatizált elkészítése a rendelések alapján.

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

### 4.1 KSH XML Struktúra

```xml
<?xml version="1.0" encoding="UTF-8"?>
<INTRASTAT>
  <HEADER>
    <PSI_ID>12345678-2-42</PSI_ID>              <!-- Adószám -->
    <REFERENCE_PERIOD>202501</REFERENCE_PERIOD>  <!-- YYYYMM -->
    <FLOW_CODE>A</FLOW_CODE>                    <!-- A=Arrival, D=Dispatch -->
    <DECLARATION_DATE>2025-02-10</DECLARATION_DATE>
    <CURRENCY_CODE>HUF</CURRENCY_CODE>
  </HEADER>

  <ITEMS>
    <ITEM>
      <LINE_NUMBER>1</LINE_NUMBER>
      <CN_CODE>12345678</CN_CODE>
      <COUNTRY_CODE>DE</COUNTRY_CODE>
      <NATURE_OF_TRANSACTION>11</NATURE_OF_TRANSACTION>
      <MODE_OF_TRANSPORT>3</MODE_OF_TRANSPORT>
      <DELIVERY_TERMS>EXW</DELIVERY_TERMS>
      <STATISTICAL_VALUE>500000</STATISTICAL_VALUE>
      <NET_MASS>125.500</NET_MASS>
      <SUPPLEMENTARY_UNIT></SUPPLEMENTARY_UNIT>
      <SUPPLEMENTARY_QUANTITY></SUPPLEMENTARY_QUANTITY>
    </ITEM>
    <!-- További tételek... -->
  </ITEMS>

  <SUMMARY>
    <TOTAL_LINES>15</TOTAL_LINES>
    <TOTAL_STATISTICAL_VALUE>7500000</TOTAL_STATISTICAL_VALUE>
    <TOTAL_NET_MASS>1875.250</TOTAL_NET_MASS>
  </SUMMARY>
</INTRASTAT>
```

### 4.2 Export Implementáció

```php
public function exportToXml(IntrastatDeclaration $declaration): string
{
    $xml = new \SimpleXMLElement('<INTRASTAT/>');

    // Header
    $header = $xml->addChild('HEADER');
    $header->addChild('PSI_ID', config('app.tax_number'));
    $header->addChild('REFERENCE_PERIOD', $declaration->reference_year . str_pad($declaration->reference_month, 2, '0', STR_PAD_LEFT));
    $header->addChild('FLOW_CODE', $declaration->direction === IntrastatDirection::ARRIVAL ? 'A' : 'D');
    $header->addChild('DECLARATION_DATE', $declaration->declaration_date->format('Y-m-d'));
    $header->addChild('CURRENCY_CODE', 'HUF');

    // Items
    $items = $xml->addChild('ITEMS');
    $lineNumber = 1;

    foreach ($declaration->intrastatLines as $line) {
        $item = $items->addChild('ITEM');
        $item->addChild('LINE_NUMBER', $lineNumber++);
        $item->addChild('CN_CODE', $line->cn_code);
        $item->addChild('COUNTRY_CODE', $line->country_of_consignment);
        $item->addChild('NATURE_OF_TRANSACTION', $line->transaction_type->value);
        $item->addChild('MODE_OF_TRANSPORT', $line->transport_mode->value);
        $item->addChild('DELIVERY_TERMS', $line->delivery_terms->value);
        $item->addChild('STATISTICAL_VALUE', (int) $line->statistical_value);
        $item->addChild('NET_MASS', number_format($line->net_mass, 3, '.', ''));

        if ($line->supplementary_unit) {
            $item->addChild('SUPPLEMENTARY_UNIT', $line->supplementary_unit);
            $item->addChild('SUPPLEMENTARY_QUANTITY', $line->supplementary_quantity);
        }
    }

    // Summary
    $summary = $xml->addChild('SUMMARY');
    $summary->addChild('TOTAL_LINES', $declaration->intrastatLines->count());
    $summary->addChild('TOTAL_STATISTICAL_VALUE', (int) $declaration->total_statistical_value);
    $summary->addChild('TOTAL_NET_MASS', number_format($declaration->total_net_mass, 3, '.', ''));

    return $xml->asXML();
}
```

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

### 5.3 Beadási Folyamat

```
1. XML exportálása
2. KSH webes felület: https://www.ksh.hu/intrastat_elektronikus_adatszolgaltatas
3. Bejelentkezés
4. XML feltöltése
5. Validáció eredmény fogadása
6. Ha OK → státusz: SUBMITTED
7. Visszaigazolás → státusz: ACCEPTED
```

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
