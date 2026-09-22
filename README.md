# Wikibar

En sikker, skrivebeskyttet genoplivning af koncertkalenderen **wikibar.dk**.

Siden bevarer det oprindelige sorte/gule design, bitmapmenuen og det komplette bevarede kalenderindhold. Den består kun af statiske HTML-, CSS-, JavaScript- og JSON-filer. Der er ingen PHP, database, login eller skriveadgang.

## Repository

- `public/` indeholder det nye statiske site.
- `legacy/2007-05-18/` indeholder den nyeste komplette kopi af den oprindelige PHP/Smarty-kode.
- `legacy/2016-12-08-partial/` indeholder den seneste, men ufuldstændige, bevarede kodekopi.

Databaseudtræk og oprindelige konfigurationshemmeligheder er ikke medtaget. Se [`legacy/README.md`](legacy/README.md).

## Data og privatliv

Dataene stammer fra den komplette databasekopi fra 8. december 2016 og indeholder events til og med 30. juni 2012. Importen medtager:

- alle 363 events, inklusive hjemmeevents
- bands og spillesteder
- offentlige brugerprofiler og de oprindelige deltagerlister
- relationen mellem events og bands

Adgangskoder, salts, tokens, mails, ændringshistorik og adresser på private hjem er udeladt. Det oprindelige SQL-dump ligger ikke i repositoryet.

`scripts/import_archive.py` kan genskabe `public/data/archive.json` fra det private dump:

```sh
python3 scripts/import_archive.py /sti/til/dump.sql
```

## Lokal kørsel

```sh
python3 -m http.server --directory public 8080
```

Åbn `http://localhost:8080`.

## Test

```sh
python3 -m unittest discover -s tests
node --test tests/test_app.js
node --check public/app.js
```

## Produktion

Indholdet af `public/` serveres direkte af nginx fra `/var/www/wikibar`. Eksempelkonfigurationen ligger i `deploy/wikibar.nginx`.
