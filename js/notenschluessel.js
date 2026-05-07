/* ─────────────────────────────────────────────────────────────
   notenschluessel.js — Tool 1: Linearer Notenschlüssel.
   ───────────────────────────────────────────────────────────── */

(function () {
  "use strict";
  if (!window.NbCore || !window.NbState || !window.NbExport) return;

  const C = window.NbCore;
  const S = window.NbState;
  const E = window.NbExport;
  const $ = (id) => document.getElementById(id);

  const TOOL = "notenschluessel";
  const SCHEMA = {
    fields: ["bestGrade", "worstGrade", "maxPts", "halfPts",
             "bestFromPts", "worstFromPts", "gradeStep"],
    radios: []
  };

  let defaults = null;
  let lastRows = null;
  let lastCfg  = null;
  let lastMeta = "";

  // ── Berechnung ──────────────────────────────────────────────
  function readCfg() {
    return {
      bestG:    C.num($("bestGrade").value),
      worstG:   C.num($("worstGrade").value),
      maxPts:   C.num($("maxPts").value),
      halfPts:  $("halfPts").value === "yes",
      bestFrom: C.num($("bestFromPts").value),
      worstFrom:C.num($("worstFromPts").value),
      stepG:    C.num($("gradeStep").value)
    };
  }

  function showError(msg) {
    $("ns_err").textContent = msg || "";
  }

  function setExportsEnabled(enabled) {
    ["ns_csv", "ns_pdf"].forEach(id => {
      const el = $(id);
      if (!el) return;
      el.disabled = !enabled;
      el.setAttribute("aria-disabled", String(!enabled));
    });
  }

  function build() {
    showError("");
    const tbody = $("ns_tbody");
    tbody.innerHTML = "";
    $("ns_table").hidden = true;
    setExportsEnabled(false);
    lastRows = null; lastCfg = null; lastMeta = "";
    $("ns_meta").textContent = "Noch keine Berechnung.";

    const cfg = readCfg();
    if (![cfg.bestG, cfg.worstG, cfg.maxPts, cfg.bestFrom, cfg.worstFrom, cfg.stepG].every(C.isFiniteNum)) {
      showError("Bitte alle Felder korrekt ausfüllen.");
      return;
    }
    if (cfg.stepG <= 0)   { showError("Notenschritt muss > 0 sein."); return; }
    if (cfg.maxPts <= 0)  { showError("Maximale Punktzahl muss größer als 0 sein."); return; }
    if (cfg.bestG === cfg.worstG) {
      showError("Beste und schlechteste Note dürfen nicht gleich sein.");
      return;
    }

    const stepP = cfg.halfPts ? 0.5 : 1;
    const stepsMax = Math.floor((cfg.maxPts + 1e-9) / stepP);
    const maxGrid  = stepsMax * stepP;

    const bestFrom  = C.clamp(cfg.bestFrom,  0, maxGrid);
    const worstFrom = C.clamp(cfg.worstFrom, 0, maxGrid);
    if (bestFrom <= worstFrom) {
      showError('"Beste ab Punkte" muss größer sein als "Schlechteste ab Punkte".');
      return;
    }

    const linCfg = { bestG: cfg.bestG, worstG: cfg.worstG, bestFrom, worstFrom };

    const pts = [];
    for (let i = stepsMax; i >= 0; i--) pts.push(i * stepP);

    const rows = [];
    let cur = null;
    for (const p of pts) {
      let g = C.linearGradeFromPoints(p, linCfg);
      g = C.roundToStep(g, cfg.stepG);
      g = C.clampMinMax(g, cfg.bestG, cfg.worstG);

      if (!cur) {
        cur = { from: p, to: p, g };
      } else if (Math.abs(cur.g - g) < 1e-9 && Math.abs(cur.to - p - stepP) < 1e-6) {
        cur.to = p;
      } else {
        rows.push(cur);
        cur = { from: p, to: p, g };
      }
    }
    if (cur) rows.push(cur);

    // Tabelle aufbauen
    const frag = document.createDocumentFragment();
    for (const r of rows) {
      const ptsLabel = (r.from === r.to)
        ? C.fmtPts(r.from, cfg.halfPts)
        : C.fmtPts(r.from, cfg.halfPts) + " – " + C.fmtPts(r.to, cfg.halfPts);
      const tr = document.createElement("tr");
      const td1 = document.createElement("td"); td1.className = "num"; td1.textContent = ptsLabel;
      const td2 = document.createElement("td"); td2.className = "num"; td2.textContent = C.fmtGrade(r.g, cfg.stepG);
      const td3 = document.createElement("td"); td3.className = "col-filler"; td3.setAttribute("aria-hidden", "true");
      tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
      frag.appendChild(tr);
    }
    tbody.appendChild(frag);
    $("ns_table").hidden = false;

    const meta = "Skala " + C.fmtGrade(cfg.bestG, 0.1) + "–" + C.fmtGrade(cfg.worstG, 0.1) +
                 " · max " + C.fmtPts(maxGrid, cfg.halfPts) +
                 " · beste ab " + C.fmtPts(bestFrom, cfg.halfPts) +
                 " · schlechteste ab " + C.fmtPts(worstFrom, cfg.halfPts);
    $("ns_meta").textContent = meta;

    lastRows = rows;
    lastCfg  = { halfPts: cfg.halfPts, stepG: cfg.stepG };
    lastMeta = meta;
    setExportsEnabled(true);

    // Automatisch in localStorage speichern: andere Tools übernehmen den
    // Schlüssel sofort beim nächsten Laden — kein extra Klick nötig.
    S.setSharedKey({
      bestG:    String(cfg.bestG),
      worstG:   String(cfg.worstG),
      maxPts:   String(cfg.maxPts),
      bestFrom: String(bestFrom),
      worstFrom:String(worstFrom),
      halfPts:  cfg.halfPts ? "yes" : "no",
      gradeStep:String(cfg.stepG)
    });
    refreshDefaultHint();
  }

  // ── CSV/PDF ─────────────────────────────────────────────────
  function exportCSV() {
    if (!lastRows) return;
    const rows = [["Punkte", "Note"]];
    for (const r of lastRows) {
      const p = (r.from === r.to)
        ? C.fmtPts(r.from, lastCfg.halfPts)
        : C.fmtPts(r.from, lastCfg.halfPts) + " – " + C.fmtPts(r.to, lastCfg.halfPts);
      rows.push([p, C.fmtGrade(r.g, lastCfg.stepG)]);
    }
    E.exportCSV(rows, "notenschluessel");
  }

  function exportPDF() {
    if (!lastRows) return;
    E.exportPDF("notenschluessel", (w) => {
      w.line("Notenschlüssel (linear)", 16, true);
      w.line(lastMeta, 10, false);
      w.blank(8);
      const tableRows = lastRows.map(r => {
        const p = (r.from === r.to)
          ? C.fmtPts(r.from, lastCfg.halfPts)
          : C.fmtPts(r.from, lastCfg.halfPts) + " – " + C.fmtPts(r.to, lastCfg.halfPts);
        return [p, C.fmtGrade(r.g, lastCfg.stepG)];
      });
      // Schmale Spalten linksbündig, restliche Seitenbreite bleibt frei.
      w.table(["Punkte", "Note"], tableRows, [0.22, 0.12]);
    });
  }

  function refreshDefaultHint() {
    const hint = $("ns_default_hint");
    if (!hint) return;
    const k = S.getSharedKey();
    if (!k) {
      hint.textContent = 'Sobald du auf "Berechnen" klickst, wird dieser Schlüssel automatisch von "Punkte → Note", "Note → Punkte" und "Klausur-Statistik" übernommen.';
    } else {
      hint.textContent = 'Schlüssel ist aktiv: Skala ' + k.bestG + '–' + k.worstG +
                         ', max ' + k.maxPts + ' Pkt. "Punkte → Note", "Note → Punkte" und "Klausur-Statistik" nutzen ihn automatisch.';
    }
  }

  // ── Reset ───────────────────────────────────────────────────
  function reset() {
    $("bestGrade").value    = "1";
    $("worstGrade").value   = "6";
    $("maxPts").value       = "60";
    $("halfPts").value      = "yes";
    $("bestFromPts").value  = "60";
    $("worstFromPts").value = "0";
    $("gradeStep").value    = "1";
    showError("");
    $("ns_table").hidden = true;
    $("ns_meta").textContent = "Noch keine Berechnung.";
    setExportsEnabled(false);
    lastRows = null; lastCfg = null; lastMeta = "";
    refreshDefaultHint();
    urlUpdate();
  }

  // ── Init ────────────────────────────────────────────────────
  let urlUpdate = function () {};

  function init() {
    defaults = S.captureDefaults(SCHEMA);
    urlUpdate = S.makeUrlUpdater(SCHEMA, defaults);

    // URL-State anwenden
    const params = S.applyFromQueryString();
    if ([...params.keys()].length) S.applyFromParams(SCHEMA, params);

    // maxPts → bestFromPts auto-sync
    $("maxPts").addEventListener("input", () => {
      const m = C.num($("maxPts").value);
      if (C.isFiniteNum(m)) $("bestFromPts").value = m;
    });

    // Berechnungen + URL-Update bei Änderungen
    const form = $("ns_form");
    form.addEventListener("input", () => { urlUpdate(); });
    form.addEventListener("change", () => { urlUpdate(); });

    $("ns_calc").addEventListener("click", build);
    $("ns_reset").addEventListener("click", reset);
    $("ns_csv").addEventListener("click", exportCSV);
    $("ns_pdf").addEventListener("click", exportPDF);

    // Enter im Formular = Berechnen
    form.addEventListener("submit", (e) => { e.preventDefault(); build(); });
    form.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && e.target.tagName !== "TEXTAREA") {
        e.preventDefault();
        build();
      }
    });

    // Snapshots
    const snaps = S.snapshots(TOOL);
    const listEl = $("ns_snap_list");
    const flashEl = $("ns_flash");
    const flash = (m) => {
      flashEl.textContent = m;
      flashEl.style.opacity = "1";
      clearTimeout(flashEl._t);
      flashEl._t = setTimeout(() => { flashEl.style.opacity = "0"; }, 1800);
    };
    function rerender() { S.renderSnapshotList(listEl, snaps, SCHEMA, (act) => {
      if (act === "apply") build();
      rerender();
    }); }
    rerender();

    $("ns_permalink").addEventListener("click", () => S.copyPermalink(SCHEMA, defaults, flashEl));
    $("ns_snap_add").addEventListener("click", () => {
      const name = prompt("Name für die Konfiguration:", "Schlüssel " + new Date().toLocaleDateString("de-DE"));
      if (name === null) return;
      snaps.add(name, SCHEMA, defaults);
      rerender();
      flash("Schnappschuss gespeichert");
    });

    refreshDefaultHint();
    build();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
