# Wikibar

En sikker, skrivebeskyttet genoplivning af koncertkalenderen **wikibar.dk** fra 2007.

Siden bevarer det oprindelige sorte/gule design, bitmapmenuen og det sidste bevarede offentlige kalenderindhold. Den består kun af statiske HTML-, CSS-, JavaScript- og JSON-filer. Der er ingen PHP, database, login eller skriveadgang.

## Data og privatliv

Dataene stammer fra databaseøjebliksbilledet 28. august 2007. Importen medtager kun:

- events
- bands
- offentlige spillesteder
- relationen mellem events og bands

Brugerkonti, password-hashes, salts, mails, profiler, deltagerlister, ændringshistorik, databasecredentials og events i private hjem er udeladt. Det oprindelige SQL-dump ligger ikke i repositoryet.

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
node --check public/app.js
```

## Produktion

Indholdet af `public/` serveres direkte af nginx fra `/var/www/wikibar`. Eksempelkonfigurationen ligger i `deploy/wikibar.nginx`.
