# Raktárkezelő Rendszer Követelmények

## 1. CIKKCSOPORTOSÍTÁS

**Célja:** A készletek átláthatóbbá tétele, a lekérdezések, kimutatások készítésének segítése.

**Követelmény:** A rendszer adjon lehetőséget a cikkek tetszőleges csoportba sorolására legalább egy szempont szerint.

**Ellenőrzés:**

- Egy cikkcsoport megtekintése a rendszerben
- Olyan lekérdezés lehívása a rendszerben, ami cikkcsoport szerinti bontásokat, kimutatásokat tartalmaz

## 2. SARZS ÉS GYÁRTÁSI SZÁM KEZELÉSE

**Célja:** A raktári készletek nyomon követhetőségének elősegítése, a szavatossággal rendelkező tételek felhasználásának optimalizálása.

**Követelmény:** A rendszer tegye lehetővé a raktáron lévő cikkekhez kapcsolódó sarzs- és gyártási számok kezelését, lehetővé téve az adott cikk mozgásának nyomon követését.

**Definíciók:**

- **Sarzs (kötegszám):** Több, egy gyártásból beérkező cikk együttes azonosítását szolgálja
- **Gyártási szám:** Cikkenként különböző, egyedi azonosítást tesz lehetővé

**Ellenőrzés:**

- Egy tetszőleges cikk sarzs- vagy gyártási szám megtekintése a rendszerben

## 3. CIKKTÖRZS KEZELÉS

**Célja:** A cikkek kezelésének egységessé tétele.

**Követelmény:** A rendszer tegye lehetővé a cikkek egységes kezelését, minimálisan az alábbi adatok tárolásával:

- Egyedi azonosító
- Cikk megnevezése
- Egyedi cikk tulajdonságok
- Cikkcsoportba sorolás
- Beszerzési adatok

**Ellenőrzés:**

- A rendszerben tetszőlegesen kiválasztott 10db cikktörzs-elem megtekintése, a minimálisan megkövetelt adattartalom ellenőrzése

## 4. TÖBB RAKTÁR KEZELÉSE

**Célja:** Az üzleti folyamatok átláthatóságának segítése.

**Követelmény:** A rendszer legyen képes, amennyiben szükséges, több (virtuális) raktár kezelésére, raktáranként tárolva az adott raktárban található készletmennyiséget.

**Ellenőrzés:**

- Egy adott cikkhez áttekinteni a rendszerben, hogy abból melyik raktárban mekkora készlet található

## 5. KÉSZLETSZINT FIGYELÉS

**Célja:** A gazdaságos működés, a raktárkészletek optimalizálásának segítése.

**Követelmény:** A rendszer adjon lehetőséget minimális és maximális készletszintek beállítására és hozzájuk kötődő automatikus figyelmeztetések megadására.

**Ellenőrzés:**

- Egy kiválasztott cikkhez kötődő minimális, maximális készletszint beállításának ellenőrzése
- Teszt eladási és beszerzési folyamat segítségével a figyelmeztetések meglétének ellenőrzése

## 6. KÉSZLETÉRTÉK VAGY MENNYISÉGI NYILVÁNTARTÁS

**Célja:** A raktárkészlet optimalizálásának elősegítése.

**Követelmény:** A rendszer adjon lehetőséget a raktárkészlet értékbeni nyilvántartására, raktáranként, cikkenként, cikk-csoportonként. A rendszer tegye lehetővé a különböző készletértékelési nyilvántartások (pl. FIFO, LIFO stb.) kezelését.

**Ellenőrzés:**

- A vállalat számviteli politikájában szereplő készletértékelési módszer szerint a készletérték megtekintése
- Az ellenőrzés időpontjában meglevő készletérték áttekintése egy adott cikkre, cikkcsoportra, raktárra

## 7. VISSZÁRU KEZELÉS

**Célja:** A hatékony logisztikai folyamatok támogatása.

**Követelmény:** A rendszer tegye lehetővé visszáru kezelését az értékesítési és beszerzési folyamatokban.

**Ellenőrzés:**

- Egy visszáru kezelési folyamat végigvitele a rendszerben: Beérkeztetés → Visszaküldés

## 8. RENDELÉSI MENNYISÉG NYILVÁNTARTÁS / RAKTÁRKÉSZLET KEZELÉS

**Célja:** A raktárkészletek optimális szinten tartása, a készletforgás tervezhetőségének növelése.

**Követelmény:** A rendszerben legyen elérhető a várható készletek beérkezésének időpontja, mennyisége, továbbá a foglaltságot is tudja kezelni. Konszignációs raktárkezelési képesség.

**Ellenőrzés:**

- Egy adott cikk várható raktárbeérkezési időpontjainak megtekintése a rendszerben

## 9. LELTÁROZÁS

**Célja:** A raktárkészlet szintek ellenőrizhetőségének támogatása.

**Követelmény:** A rendszer tegye lehetővé:

- Leltárívek
- Leltárkészlet korrekció
- Leltárciklusok kezelését, használatát

**Ellenőrzés:**

- A leltár ív nyomtatása a rendszerből
- Teszt-leltár rögzítése egy 10 elemű cikk részhalmazra, eltérésekkel és leltárkészlet korrekció ellenőrzéssel:
  - Raktárkészlet hiány rögzítése
  - Raktárkészlet többlet rögzítése

## 10. ÁRLISTA KEZELÉS

**Célja:** A beszerzési folyamatok segítése, a cég rugalmasságának növelése.

**Követelmény:** A rendszer tegye lehetővé:

- Beszerzési árlisták kezelését, lehetőséget adva szállítónként egyedi árak megadására is
- Árlisták közvetlenül fájlból (XLS, XML, TXT, CSV, stb.) történő betöltését

**Ellenőrzés:**

- Egy cikk megvizsgálása, ahol a cikknek tartalmaznia kell két különböző beszerzési árat
  - Például csavar: A) szállítótól 3 Ft, B) szállítótól 2,5 Ft

## 11. INTRASTAT ADATSZOLGÁLTATÁS

**Célja:** Az INTRASTAT adatszolgáltatás bevallás elkészítése.

**Követelmény:** A rendszer tegye lehetővé az INTRASTAT bevalláshoz szükséges adatok összegyűjtését és a bevallás elkészítését.

**Ellenőrzés:**

- A rendszer által előállított és beadott INTRASTAT adatszolgáltatás elkészítési lépéseinek áttekintése

## 12. LOGISZTIKAI LÁNC VÉGIGKÖVETÉSE

**Célja:** A munkavégzés segítése, egyszerűbb, hatékonyabb munkavégzés lehetővé tétele.

**Követelmény:** A rendszer támogassa a beszerzők munkáját az adott, teljes beszerzési folyamatra vonatkozóan: **Rendelés → Szállítás → Számlázás**

A beszerzési bizonylatok adatait csak egyszer kelljen bevinni a rendszerbe, az egymást követő bizonylatok az előzőekből logikailag származtathatóak legyenek. Továbbá rendelkezzen integrációs lehetőséggel a kapcsolódó pénzügyi (elszámolási / fizetési) modul(ok)hoz.

**Ellenőrzés:**

- Egy tetszőlegesen kiválasztott logisztikai folyamat megtekintése, ahol az egymást követő bizonylatok már tartalmazzák a korábban bevitt releváns adatokat
