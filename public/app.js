"use strict";

const main = document.querySelector("#main");
const params = new URLSearchParams(location.search);
const view = params.get("view") || "calendar";
const id = Number(params.get("id"));
const requestedPage = Math.max(1, Number.parseInt(params.get("page") || "1", 10) || 1);
const EVENTS_PER_PAGE = 12;

const labels = {
  concert: "koncert",
  party: "fest",
  releaseparty: "releasefest",
  release: "udgivelse",
  festival: "festival",
};

const selectedNav = view === "venue" ? "venues" : view === "band" ? "bands" : view === "user" ? "users" : view;
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
  if (item.facebook) {
    const facebook = externalLink("Facebook", item.facebook);
    facebook.className = "social-text";
    wrapper.append(" ", facebook);
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
  const users = new Map(data.users.map((user) => [user.id, user]));
  const attendance = new Map();
  for (const entry of data.attendance) {
    if (!attendance.has(entry.eventId)) attendance.set(entry.eventId, []);
    attendance.get(entry.eventId).push(entry.userId);
  }
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

    const eventUsers = (attendance.get(event.id) || []).map((userId) => users.get(userId)).filter(Boolean);
    if (eventUsers.length) {
      const guestList = node("div", { className: "eventguests" });
      eventUsers.forEach((user, index) => {
        guestList.append(entityLink(user, "user"));
        guestList.append(index < eventUsers.length - 1 ? " • " : " var på");
      });
      details.append(guestList);
    }
    eventContent.append(details);

    if (event.text || event.website || event.myspace || event.facebook) {
      const notes = node("div", { className: "eventnotes" });
      if (event.text) notes.append(node("span", { className: "content-copy", text: event.text }));
      const eventLinks = [event.website, event.myspace, event.facebook].filter(Boolean);
      if (eventLinks.length) {
        const links = node("div", { className: "eventlinks" });
        eventLinks.forEach((url, index) => {
          if (index) links.append(" • ");
          links.append(externalLink(url.replace(/^https?:\/\//, ""), url));
        });
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

function pager(totalPages, currentPage) {
  const bar = node("nav", { className: "editbar pager" });
  bar.setAttribute("aria-label", "Kalendersider");
  const first = Math.max(1, currentPage - 2);
  const last = Math.min(totalPages, currentPage + 2);
  const addPage = (page, label = `Side ${page}`) => {
    const query = new URLSearchParams(location.search);
    query.set("page", page);
    const link = node("a", { className: `button${page === currentPage ? " activebutton" : ""}`, text: label, href: `?${query}` });
    if (page === currentPage) link.setAttribute("aria-current", "page");
    bar.append(link);
  };
  if (first > 1) addPage(1);
  if (first > 2) bar.append(" … ");
  for (let page = first; page <= last; page += 1) {
    if (bar.childNodes.length) bar.append(" ");
    addPage(page);
  }
  if (last < totalPages - 1) bar.append(" … ");
  if (last < totalPages) {
    bar.append(" ");
    addPage(totalPages);
  }
  return bar;
}

function showCalendar(data) {
  const totalPages = Math.ceil(data.events.length / EVENTS_PER_PAGE);
  const page = Math.min(requestedPage, totalPages);
  const events = data.events.slice((page - 1) * EVENTS_PER_PAGE, page * EVENTS_PER_PAGE);
  heading(`Kalender${page > 1 ? ` (side ${page})` : ""}`);
  const wrapper = content();
  wrapper.append(eventRows(data, events), pager(totalPages, page));
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
    infoHeading(box, item.privateHome ? "Type" : "Adresse", item.privateHome ? "Privat hjem" : address);
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
  if (item.facebook) infoHeading(box, "Facebook", externalLink(item.facebook.replace(/^https?:\/\//, ""), item.facebook));
  if (box.children.length) wrapper.append(box);
  if (item.text) wrapper.append(node("p", { className: "content-copy", text: item.text }));

  const matchingEventIds = type === "band"
    ? new Set(data.appearances.filter((entry) => entry.bandId === item.id).map((entry) => entry.eventId))
    : null;
  const events = data.events.filter((event) => type === "band" ? matchingEventIds.has(event.id) : event.venueId === item.id);
  if (events.length) {
    wrapper.append(node("h2", { className: "clear", text: "Tidligere events" }));
    wrapper.append(eventRows(data, events.slice(-6).reverse()));
  } else {
    wrapper.append(node("em", { text: "Kalenderen er tom" }));
  }
  wrapper.append(node("div", { className: "clear" }));
}

function showUsers(data) {
  heading("Brugere");
  const wrapper = content();
  const list = node("ol", { className: "columns users" });
  list.append(...data.users.map((user) => node("li", {}, [entityLink(user, "user")])));
  wrapper.append(list);
}

function showUser(data, userId) {
  const user = data.users.find((entry) => entry.id === userId);
  if (!user) return showNotFound();
  heading(`${user.name} (bruger)`);
  const wrapper = content();
  const box = node("aside", { className: "info" });
  if (user.website) infoHeading(box, "Website", externalLink(user.website.replace(/^https?:\/\//, ""), user.website));
  if (user.myspace) infoHeading(box, "MySpace", externalLink(user.myspace.replace(/^https?:\/\//, ""), user.myspace));
  if (user.facebook) infoHeading(box, "Facebook", externalLink(user.facebook.replace(/^https?:\/\//, ""), user.facebook));
  if (box.children.length) wrapper.append(box);
  if (user.text) wrapper.append(node("p", { className: "content-copy", text: user.text }));
  else wrapper.append(node("em", { text: "Denne bruger skrev ikke en profiltekst." }));

  const eventIds = new Set(data.attendance.filter((entry) => entry.userId === user.id).map((entry) => entry.eventId));
  const events = data.events.filter((event) => eventIds.has(event.id));
  if (events.length) {
    wrapper.append(node("h2", { className: "clear", text: "Tidligere events" }));
    wrapper.append(eventRows(data, events.slice(-6).reverse()));
  }
  wrapper.append(node("div", { className: "clear" }));
}

function showAbout() {
  heading("Om wikibar");
  const wrapper = content();
  wrapper.append(
    node("p", { text: "Wikibar var en fælles koncertkalender, hvor brugerne kunne oprette og redigere events, bands og spillesteder – og fortælle, hvilke koncerter de tog til." }),
    node("p", { text: "Denne udgave genskaber designet og indholdet fra den komplette bevarede databasekopi fra 8. december 2016. Den kører som statiske filer uden PHP, MySQL, login eller skriveadgang." }),
    node("p", { text: "Alle events, bands, spillesteder, offentlige profiloplysninger og de oprindelige deltagerlister er med. Adgangskoder, mails, tokens, ændringshistorik og adresser på private hjem er udeladt." }),
  );
}

function showStats(data) {
  heading("Statistik");
  const wrapper = content();
  const table = node("table", { className: "stats" });
  for (const [label, value] of [["Events", data.events.length], ["Bands", data.bands.length], ["Spillesteder", data.venues.length], ["Brugere", data.users.length]]) {
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
    else if (view === "users") showUsers(data);
    else if (view === "user") showUser(data, id);
    else if (view === "about") showAbout();
    else if (view === "stats") showStats(data);
    else showNotFound();
    main.focus({ preventScroll: true });
  })
  .catch((error) => {
    heading("Fejl");
    content().append(node("div", { className: "notice errortext" }, [node("div", { text: `Kunne ikke indlæse arkivet: ${error.message}` })]));
  });
