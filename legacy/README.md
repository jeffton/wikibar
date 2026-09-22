# Oprindelig Wikibar-kode

Denne mappe bevarer den gamle PHP/Smarty-kode uden modernisering.

- `2007-05-18/` er den nyeste komplette kildekodekopi med desktop-sitet, grafik, JavaScript, Smarty og UTF-8-biblioteket.
- `2016-12-08-partial/` er den seneste bevarede kodekopi. Arkivet var allerede ufuldstændigt, men viser de senere API-, historik-, mobil-, statistik- og Facebook-ændringer.

De oprindelige databaseudtræk er ikke medtaget. Konfigurationsfiler og tre alternative test-/produktionsfiler med hardkodede databaseoplysninger er erstattet af `.example`-filer. Genererede Smarty-filer i `templates_c`, lokale projektfiler og `.DS_Store` er udeladt.

`MANIFEST.sha256` fastholder indholdet af begge kildekodekopier.

Koden kræver den oprindelige PHP/MySQL-runtime og er kun bevaret som historisk kildekode. Det nye statiske site ligger i `public/`.
