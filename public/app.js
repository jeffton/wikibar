"use strict";

const main = document.querySelector("#main");
const params = new URLSearchParams(location.search);
const view = params.get("view") || "calendar";
const id = Number(params.get("id"));

const labels = {
  concert: "koncert",
  party: "fest",
  releaseparty: "releasefest",
  release: "udgivelse",
  festival: "festival",
};

const selectedNav = view === "venue" ? "venues" : view === "band" ? "bands" : view;
for (const link of document.querySelectorAll("[data-nav]")) {
  const nav = link.dataset.nav;
  const image = link.querySelector("img");
  image.src = `layout/menu_${nav === "venues" ? "venue" : nav === "bands" ? "band" : nav === "users" ? "user" : "calendar"}${nav === selectedNav ? "_selected" : ""}.png`;
}

const dateFormatter = new Intl.DateTimeFormat("da-DK", {
  weekday: "long", day: "numeric", month: "long", year: "numeric", timeZone: "Europe/Copenhagen",
});
document.querySelector("#today").textContent = new Intl.DateTimeFormat("da-DK", {
  day: "numeric", month: "long", year: "numeric", timeZone: "Europe/Copenhagen",
}).format(new Date());

function node(tag, options = {}, children = []) {
  const element = document.createElement(tag);
  if (options.className) element.className = options.className;
  if (options.text !== undefined) element.textContent = options.text;
  if (options.href) element.href = options.href;
  if (options.title) element.title = options.title;
  for (const child of children) {
    if (child !== null && child !== undefined) element.append(child);
  }
  return element;
}

function route(label, targetView, targetId) {
  const query = new URLSearchParams({ view: targetView });
  if (targetId !== undefined) query.set("id", targetId);
  return node("a", { text: label, href: `?${query}` });
}

function entityLink(item, type) {
  const wrapper = node("span", { className: "item" }, [route(item.name, type, item.id)]);
  for (const [url, image, label, width, height] of [
    [item.website, "linkicon-website.png", "Website", 18, 18],
    [item.myspace, "linkicon-myspace.png", "MySpace", 17, 19],
  ]) {
    if (!url) continue;
    const icon = node("img");
    icon.src = `layout/${image}`;
    icon.alt = label;
    icon.title = label;
    icon.width = width;
    icon.height = height;
    const link = node("a", { href: url }, [icon]);
    link.target = "_blank";
    link.rel = "noreferrer";
    wrapper.append(" ", link);
  }
  return wrapper;
}

function heading(title) {
  document.title = `${title} på wikibar.dk`;
  main.replaceChildren(node("h1", { text: title }));
}

function content() {
  const wrapper = node("div", { className: "content" });
  main.append(wrapper);
  return wrapper;
}

function notice(text) {
  return node("div", { className: "notice infotext archive-note" }, [node("div", { text })]);
}

function archiveDate(value) {
  return dateFormatter.format(new Date(`${value}T12:00:00+02:00`));
}

function externalLink(label, url) {
  const link = node("a", { text: label, href: url });
  link.target = "_blank";
  link.rel = "noreferrer";
  return link;
}

function eventRows(data, events) {
  const bands = new Map(data.bands.map((band) => [band.id, band]));
  const venues = new Map(data.venues.map((venue) => [venue.id, venue]));
  const appearances = new Map();
  for (const appearance of data.appearances) {
    if (!appearances.has(appearance.eventId)) appearances.set(appearance.eventId, []);
    appearances.get(appearance.eventId).push(appearance);
  }
  for (const list of appearances.values()) list.sort((a, b) => a.sequence - b.sequence);

  const calendar = node("div", { className: "calendar" });
  let previousDate = null;
  for (const event of events) {
    if (event.date !== previousDate || event.endDate) {
      const dateHeading = node("h2", { text: archiveDate(event.date) });
      if (event.endDate) {
        dateHeading.append(" ", node("span", { className: "item" }, [
          node("small", { text: `til ${archiveDate(event.endDate)}` }),
        ]));
      }
      calendar.append(dateHeading);
    }
    previousDate = event.date;

    const card = node("article", { className: "event" });
    card.style.backgroundImage = `url("layout/event-${event.type}.png")`;
    card.setAttribute("aria-label", labels[event.type] || event.type);

    const eventContent = node("div", { className: "eventcontent" });
    eventContent.append(node("div", { className: "minibar" }, [
      node("a", { className: "button", text: "Redigér event", href: "?view=about" }),
      node("a", { className: "button", text: "Jeg er på!", href: "?view=users" }),
    ]));

    if (event.status) {
      const statusImage = node("img", { className: "eventstatus" });
      statusImage.src = `layout/eventstatus_${event.status}.png`;
      statusImage.alt = event.status === "soldout" ? "UDSOLGT" : "AFLYST";
      statusImage.width = 86;
      statusImage.height = 40;
      eventContent.append(statusImage);
    }

    const details = node("div", { className: "eventdetails" });
    if (event.name) details.append(node("h3", { text: event.name }));

    const eventBands = (appearances.get(event.id) || []).map((item) => bands.get(item.bandId)).filter(Boolean);
    if (eventBands.length) {
      const bandLine = node("div", { className: "eventbands" });
      eventBands.forEach((band, index) => {
        bandLine.append(entityLink(band, "band"));
        if (index < eventBands.length - 1) bandLine.append(" • ");
      });
      details.append(bandLine);
    }

    const meta = node("div", { className: "eventmeta" });
    if (event.time) meta.append(event.time.slice(0, 5));
    const venue = venues.get(event.venueId);
    if (venue) {
      if (meta.childNodes.length) meta.append(" ");
      meta.append(node("span", { className: "at", text: "@" }), entityLink(venue, "venue"));
    }
    if (event.price !== null) {
      if (meta.childNodes.length) meta.append(" – ");
      meta.append(`${event.price} kr.`);
    }
    if (meta.childNodes.length) details.append(meta);
    eventContent.append(details);

    if (event.text || event.website || event.myspace) {
      const notes = node("div", { className: "eventnotes" });
      if (event.text) notes.append(node("span", { className: "content-copy", text: event.text }));
      if (event.website || event.myspace) {
        const links = node("div", { className: "eventlinks" });
        if (event.website) links.append(externalLink(event.website.replace(/^https?:\/\//, ""), event.website));
        if (event.website && event.myspace) links.append(" • ");
        if (event.myspace) links.append(externalLink(event.myspace.replace(/^https?:\/\//, ""), event.myspace));
        notes.append(links);
      }
      eventContent.append(notes);
    }

    eventContent.append(node("div", { className: "clear" }));
    card.append(eventContent);
    calendar.append(card);
  }
  return calendar;
}

function showCalendar(data, events = data.events, title = "Kalender") {
  heading(title);
  const wrapper = content();
  wrapper.append(notice("Koncertkalenderen står på det sidste dataøjebliksbillede fra 28. august 2007."));
  if (events.length) wrapper.append(eventRows(data, events));
  else wrapper.append(node("em", { text: "Kalenderen er tom" }));
}

function showBrowse(data, type) {
  const items = type === "bands" ? data.bands : data.venues;
  const singular = type === "bands" ? "band" : "venue";
  heading(type === "bands" ? "Bands" : "Spillesteder");
  const wrapper = content();
  wrapper.append(node("p", { text: "Vælg begyndelsesbogstaver eller søg:" }));

  const form = node("form", { className: "searchform" });
  const input = node("input");
  input.type = "search";
  input.name = "search";
  input.placeholder = "Søg";
  input.setAttribute("aria-label", "Søg");
  form.append(input, node("button", { className: "button", text: "Søg" }));
  wrapper.append(form);

  const list = node("ol", { className: "columns" });
  const draw = () => {
    const query = input.value.trim().toLocaleLowerCase("da");
    const matches = items.filter((item) => item.name.toLocaleLowerCase("da").includes(query));
    list.replaceChildren(...matches.map((item) => node("li", {}, [entityLink(item, singular)])));
    if (!matches.length) list.append(node("li", { className: "empty", text: "(ingen resultater)" }));
  };
  form.addEventListener("submit", (event) => { event.preventDefault(); draw(); });
  input.addEventListener("input", draw);
  draw();
  wrapper.append(list);
}

function infoHeading(box, title, value) {
  if (value === null || value === undefined || value === "") return;
  box.append(node("h2", { text: title }), typeof value === "string" ? node("div", { text: value }) : value);
}

function showDetail(data, type, itemId) {
  const collection = type === "band" ? data.bands : data.venues;
  const item = collection.find((entry) => entry.id === itemId);
  if (!item) return showNotFound();

  heading(`${item.name} (${type === "band" ? "band" : "spillested"})`);
  const wrapper = content();
  const box = node("aside", { className: "info" });

  if (type === "venue") {
    const address = [item.street, [item.postalCode, item.city].filter(Boolean).join(" "), item.country].filter(Boolean).join("\n");
    infoHeading(box, "Adresse", address);
    infoHeading(box, "Musikken starter", item.musicStarts);
    const prices = [
      ["Typisk entré", item.typicalEntry], ["Fadøl", item.draught], ["Flaskeøl", item.bottle],
      ["Shot", item.shot], ["Typisk drink", item.drink],
    ].filter(([, value]) => value !== null);
    if (prices.length) {
      const table = node("table");
      for (const [label, value] of prices) table.append(node("tr", {}, [node("th", { text: label }), node("td", { className: "amount", text: `${value} kr.` })]));
      infoHeading(box, "Priser", table);
    }
    if (item.wardrobe !== null) infoHeading(box, "Garderobe", item.wardrobe ? (item.wardrobePrice === null ? "Ja" : `Ja, ${item.wardrobePrice} kr.`) : "Nej");
  } else {
    infoHeading(box, "Land", item.country);
  }
  if (item.website) infoHeading(box, "Website", externalLink(item.website.replace(/^https?:\/\//, ""), item.website));
  if (item.myspace) infoHeading(box, "MySpace", externalLink(item.myspace.replace(/^https?:\/\//, ""), item.myspace));
  if (box.children.length) wrapper.append(box);
  if (item.text) wrapper.append(node("p", { className: "content-copy", text: item.text }));

  const matchingEventIds = type === "band"
    ? new Set(data.appearances.filter((entry) => entry.bandId === item.id).map((entry) => entry.eventId))
    : null;
  const events = data.events.filter((event) => type === "band" ? matchingEventIds.has(event.id) : event.venueId === item.id);
  if (events.length) wrapper.append(eventRows(data, events));
  else wrapper.append(node("em", { text: "Kalenderen er tom" }));
  wrapper.append(node("div", { className: "clear" }));
}

function showUsers() {
  heading("Brugere");
  const wrapper = content();
  wrapper.append(notice("Brugerkonti og login er ikke genåbnet."));
  wrapper.append(node("p", { text: "Den oprindelige brugerdatabase, adgangskoder, profiler og deltagerlister er udeladt. Koncertkalenderen er bevaret som et skrivebeskyttet arkiv." }));
}

function showAbout() {
  heading("Om wikibar");
  const wrapper = content();
  wrapper.append(
    node("p", { text: "Wikibar var en fælles koncertkalender, hvor brugerne kunne oprette og redigere events, bands og spillesteder – og fortælle, hvilke koncerter de tog til." }),
    node("p", { text: "Denne udgave genskaber designet og det offentlige kalenderindhold fra det sidste bevarede databaseudtræk den 28. august 2007. Den kører som statiske filer uden PHP, MySQL, login eller skriveadgang." }),
    node("p", { text: "Konti, adgangskoder, mails, deltagerlister, ændringshistorik og private hjemmeevents er ikke medtaget." }),
  );
}

function showStats(data) {
  heading("Statistik");
  const wrapper = content();
  wrapper.append(notice("Tal fra det rensede arkivøjebliksbillede."));
  const table = node("table", { className: "stats" });
  for (const [label, value] of [["Events", data.events.length], ["Bands", data.bands.length], ["Spillesteder", data.venues.length]]) {
    table.append(node("tr", {}, [node("th", { text: label }), node("td", { text: String(value) })]));
  }
  wrapper.append(table);
}

function showNotFound() {
  heading("Ikke fundet");
  content().append(node("p", {}, [document.createTextNode("Siden findes ikke. Gå tilbage til "), route("kalenderen", "calendar"), document.createTextNode(".")]));
}

fetch("data/archive.json")
  .then((response) => {
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.json();
  })
  .then((data) => {
    if (view === "calendar") showCalendar(data);
    else if (view === "bands" || view === "venues") showBrowse(data, view);
    else if (view === "band" || view === "venue") showDetail(data, view, id);
    else if (view === "users") showUsers();
    else if (view === "about") showAbout();
    else if (view === "stats") showStats(data);
    else showNotFound();
    main.focus({ preventScroll: true });
  })
  .catch((error) => {
    heading("Fejl");
    content().append(node("div", { className: "notice errortext" }, [node("div", { text: `Kunne ikke indlæse arkivet: ${error.message}` })]));
  });
