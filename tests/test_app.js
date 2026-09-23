"use strict";

const assert = require("node:assert/strict");
const fs = require("node:fs");
const test = require("node:test");

const source = fs.readFileSync("public/app.js", "utf8");
const start = source.indexOf("function browseRanges");
const end = source.indexOf("\n\nfunction showBrowse", start);
const browseRanges = eval(`${source.slice(start, end)}\nbrowseRanges`);
const hrefStart = source.indexOf("function hrefFor");
const hrefEnd = source.indexOf("\n\nfunction iconLink", hrefStart);
const hrefFor = eval(`${source.slice(hrefStart, hrefEnd)}\nhrefFor`);
const routeStart = source.indexOf("function routePath");
const routeEnd = source.indexOf("\n\nfunction route(", routeStart);
const routePath = eval(`${source.slice(routeStart, routeEnd)}\nroutePath`);
const archive = JSON.parse(fs.readFileSync("public/data/archive.json", "utf8"));

function labels(items, perPage) {
  return browseRanges(items, perPage).map((range) => range.last ? `${range.first} – ${range.last}` : range.first);
}

test("band page ranges use the shortest unique boundaries", () => {
  assert.deepEqual(labels(archive.bands, 30), [
    "1 – Bob", "Bod – Cri", "Cry – Dragon", "Dragonf – Flo", "Fly – Il", "In – K",
    "L – Mar", "May – My", "Mú – Pit", "Pix – Sca", "Scr – Strange",
    "Stranger – Twil", "Twin – Z",
  ]);
});

test("outer and Unicode boundaries are shortened to one character", () => {
  const items = ["Æble", "Ørn", "Ål", "Åse"].map((sortName, id) => ({ id, name: sortName, sortName }));
  assert.deepEqual(labels(items, 2), ["Æ – Ø", "Å"]);
});

test("legacy paths cover profiles, browse, calendar, and static pages", () => {
  assert.equal(routePath("venue", 13), "/venue/13");
  assert.equal(routePath("band", 1), "/band/1");
  assert.equal(routePath("user", 1), "/user/1");
  assert.equal(routePath("venues", 2), "/venues/2");
  assert.equal(routePath("bands"), "/bands");
  assert.equal(routePath("calendar", 1), "/");
  assert.equal(routePath("calendar", 2), "/2");
  assert.equal(routePath("calendar", -1), "/-1");
  assert.equal(routePath("stats"), "/page/stats");
});

test("raw archive URL values become functional links only at render time", () => {
  assert.equal(hrefFor("decoratedecorate.com"), "https://decoratedecorate.com");
  assert.equal(hrefFor("decoratedecorate", "myspace"), "https://myspace.com/decoratedecorate");
  assert.equal(hrefFor("pages/Beta-Satan/6280687935", "facebook"), "https://facebook.com/pages/Beta-Satan/6280687935");
});
