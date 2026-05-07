/* ─────────────────────────────────────────────────────────────
   hub.js — Übersichtsseite: zeigt alle gespeicherten Konfigurationen
   aus allen Tools, sortiert nach Datum. Klick auf "Laden" springt
   zum jeweiligen Tool und stellt die Konfiguration über den
   Permalink wieder her.
   ───────────────────────────────────────────────────────────── */

(function () {
  "use strict";
  if (!window.NbCore) return;
  const C = window.NbCore;
  const $ = (id) => document.getElementById(id);

  // Tools mit Snapshot-Funktion (Beurteilung & Würfeln haben keine).
  const TOOLS = [
    { key: "notenschluessel", url: "notenschluessel", label: "Notenschlüssel" },
    { key: "punkte-note",     url: "punkte-note",     label: "Punkte → Note"  },
    { key: "note-punkte",     url: "note-punkte",     label: "Note → Punkte"  },
    { key: "statistik",       url: "statistik",       label: "Klausur-Statistik" },
    { key: "blocknoten",      url: "blocknoten",      label: "Blocknoten"     }
  ];

  function safeArray(raw) {
    try {
      const v = JSON.parse(raw);
      return Array.isArray(v) ? v : [];
    } catch (e) {
      return [];
    }
  }

  function listAll() {
    const all = [];
    for (const t of TOOLS) {
      const raw = localStorage.getItem("nb_snap_" + t.key);
      if (!raw) continue;
      const arr = safeArray(raw);
      arr.forEach((s, i) => {
        if (s && typeof s.name === "string" && typeof s.data === "string") {
          all.push({
            toolKey:   t.key,
            toolUrl:   t.url,
            toolLabel: t.label,
            idx:       i,
            name:      s.name,
            data:      s.data,
            ts:        Number(s.ts) || 0
          });
        }
      });
    }
    all.sort((a, b) => b.ts - a.ts);
    return all;
  }

  function formatDate(ts) {
    if (!ts) return "";
    const d = new Date(ts);
    const pad = (x) => String(x).padStart(2, "0");
    return pad(d.getDate()) + "." + pad(d.getMonth() + 1) + "." + d.getFullYear() +
           " " + pad(d.getHours()) + ":" + pad(d.getMinutes());
  }

  function removeSnapshot(toolKey, idx) {
    const storeKey = "nb_snap_" + toolKey;
    const arr = safeArray(localStorage.getItem(storeKey));
    arr.splice(idx, 1);
    try { localStorage.setItem(storeKey, JSON.stringify(arr)); } catch (e) {}
  }

  function render() {
    const listEl  = $("hub_snap_list");
    const emptyEl = $("hub_snap_empty");
    if (!listEl || !emptyEl) return;
    listEl.innerHTML = "";

    const items = listAll();
    if (items.length === 0) {
      emptyEl.hidden = false;
      listEl.hidden  = true;
      return;
    }
    emptyEl.hidden = true;
    listEl.hidden  = false;

    for (const s of items) {
      const li = document.createElement("li");
      li.className = "hub-snap-item";

      const meta = document.createElement("span");
      meta.className = "hub-snap-meta";
      const tool = document.createElement("strong");
      tool.textContent = s.toolLabel;
      meta.appendChild(tool);
      meta.appendChild(document.createTextNode(" · " + s.name));
      if (s.ts) {
        const date = document.createElement("em");
        date.className = "hub-snap-date";
        date.textContent = "  " + formatDate(s.ts);
        meta.appendChild(date);
      }

      const loadLink = document.createElement("a");
      loadLink.className = "btn-tiny";
      loadLink.href = s.toolUrl + (s.data ? "?" + s.data : "");
      loadLink.textContent = "laden";

      const delBtn = document.createElement("button");
      delBtn.type = "button";
      delBtn.className = "btn-tiny";
      delBtn.setAttribute("aria-label",
        'Konfiguration "' + s.name + '" für "' + s.toolLabel + '" entfernen');
      delBtn.textContent = "×";
      delBtn.addEventListener("click", () => {
        removeSnapshot(s.toolKey, s.idx);
        render();
      });

      li.appendChild(meta);
      li.appendChild(loadLink);
      li.appendChild(delBtn);
      listEl.appendChild(li);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", render);
  } else {
    render();
  }
})();
