"use strict";

const assert = require("node:assert/strict");
const fs = require("node:fs");
const test = require("node:test");

const source = fs.readFileSync("public/app.js", "utf8");
const start = source.indexOf("function browseRanges");
const end = source.indexOf("\n\nfunction showBrowse", start);
const browseRanges = eval(`${source.slice(start, end)}\nbrowseRanges`);
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
