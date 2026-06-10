/* ─────────────────────────────────────────────────────────────
   statistik.js — Tool 4: Klausur-Statistik & Auswertung.
   Kennzahlen + Verteilungstabelle (kein Histogramm — Vereinfachung).
   ───────────────────────────────────────────────────────────── */

(function () {
  "use strict";
  if (!window.NbCore || !window.NbState || !window.NbExport) return;

  const C = window.NbCore;
  const S = window.NbState;
  const E = window.NbExport;
  const $ = (id) => document.getElementById(id);

  const TOOL = "statistik";
  const SCHEMA = {
    fields: ["ksValues","ksMode","ksGradeStep","ksUseKey","ksPassThreshold",
             "ksBestG","ksWorstG","ksMaxPts","ksBestFrom","ksWorstFrom"],
    radios: []
  };

  let defaults = null;
  let urlUpdate = function () {};
  let lastResult = null;

  function refreshKeyHint() {
    const hint = $("ks_key_hint");
    if (!hint) return;
    if ($("ksUseKey").value !== "yes") { hint.textContent = ""; return; }
    const k = S.getSharedKey();
    if (k) {
      hint.textContent = "Aktueller Schlüssel: Skala " + k.bestG + "–" + k.worstG +
                         ", max " + k.maxPts + " Pkt.";
    } else {
      hint.textContent = 'Noch kein Schlüssel im "Notenschlüssel"-Tool angelegt — eigene Werte aktiv.';
    }
  }

  function applyStoredKey() {
    if ($("ksUseKey").value !== "yes") { refreshKeyHint(); return; }
    const k = S.getSharedKey();
    if (!k) {
      $("ksUseKey").value = "no";
      refreshKeyHint();
      return;
    }
    if (k.bestG     !== undefined) $("ksBestG").value     = k.bestG;
    if (k.worstG    !== undefined) $("ksWorstG").value    = k.worstG;
    if (k.maxPts    !== undefined) $("ksMaxPts").value    = k.maxPts;
    if (k.bestFrom  !== undefined) $("ksBestFrom").value  = k.bestFrom;
    if (k.worstFrom !== undefined) $("ksWorstFrom").value = k.worstFrom;
    if (k.gradeStep !== undefined) $("ksGradeStep").value = k.gradeStep;
    refreshKeyHint();
  }

  function toggleOwnFields() {
    const own = $("ksUseKey").value === "no";
    ["ksBestG","ksWorstG","ksMaxPts","ksBestFrom","ksWorstFrom","ksGradeStep"].forEach(id => {
      $(id).disabled = !own;
    });
  }

  function showError(msg) { $("ks_err").textContent = msg || ""; }

  // ── Parsing der Eingabe ─────────────────────────────────────
  function parseValues(text) {
    if (!text) return [];
    const tokens = String(text).split(/[\s,;]+/).filter(Boolean);
    const out = [];
    for (const t of tokens) {
      const n = Number(t.replace(",", "."));
      if (Number.isFinite(n)) out.push(n);
    }
    return out;
  }

  // ── Statistik ───────────────────────────────────────────────
  function statistics(values) {
    const n = values.length;
    if (n === 0) return null;
    const sorted = values.slice().sort((a, b) => a - b);
    const sum = values.reduce((s, x) => s + x, 0);
    const mean = sum / n;
    const min = sorted[0];
    const max = sorted[n - 1];
    const range = max - min;
    let median;
    if (n % 2 === 0) median = (sorted[n / 2 - 1] + sorted[n / 2]) / 2;
    else             median = sorted[(n - 1) / 2];
    let variance = 0;
    for (const x of values) variance += (x - mean) * (x - mean);
    variance = n > 1 ? variance / (n - 1) : 0; // Stichproben-Varianz
    const stdev = Math.sqrt(variance);
    // Modus: häufigster gerundeter Wert
    const counts = new Map();
    for (const x of values) {
      const k = String(Math.round(x * 100) / 100);
      counts.set(k, (counts.get(k) || 0) + 1);
    }
    let maxC = 0, modeKey = null;
    for (const [k, c] of counts) if (c > maxC) { maxC = c; modeKey = k; }
    const modeVal = modeKey !== null ? Number(modeKey) : NaN;

    return { n, sum, mean, median, mode: modeVal, min, max, range, stdev };
  }

  function pointsToGrade(p, cfg) {
    const stepP = cfg.halfPts ? 0.5 : 1;
    const stepsMax = Math.floor((cfg.maxPts + 1e-9) / stepP);
    const maxGrid = stepsMax * stepP;
    const bF = C.clamp(cfg.bestFrom,  0, maxGrid);
    const wF = C.clamp(cfg.worstFrom, 0, maxGrid);
    let pp = C.clamp(p, 0, maxGrid);
    pp = Math.round(pp / stepP) * stepP;
    let g = C.linearGradeFromPoints(pp, { bestG: cfg.bestG, worstG: cfg.worstG, bestFrom: bF, worstFrom: wF });
    g = C.clampMinMax(C.roundToStep(g, cfg.stepG), cfg.bestG, cfg.worstG);
    return g;
  }

  function distribution(grades, stepG, bestG, worstG) {
    const lo = Math.min(bestG, worstG);
    const hi = Math.max(bestG, worstG);
    const buckets = new Map();
    for (const g of grades) {
      let r = C.roundToStep(g, stepG);
      r = C.clampMinMax(r, lo, hi);
      const k = Number(r.toFixed(4));
      buckets.set(k, (buckets.get(k) || 0) + 1);
    }
    const sorted = [...buckets.entries()].sort((a, b) => a[0] - b[0]);
    const total = grades.length;
    return sorted.map(([k, c]) => ({ grade: k, count: c, share: total ? c / total : 0 }));
  }

  // ── Hauptberechnung ─────────────────────────────────────────
  function build() {
    showError("");
    $("ks_summary").hidden = true;
    $("ks_dist").hidden = true;
    $("ks_summary_body").innerHTML = "";
    $("ks_dist_body").innerHTML = "";
    $("ks_csv").disabled = true; $("ks_csv").setAttribute("aria-disabled", "true");
    $("ks_pdf").disabled = true; $("ks_pdf").setAttribute("aria-disabled", "true");
    lastResult = null;

    const text = $("ksValues").value;
    const raw = parseValues(text);
    if (raw.length === 0) {
      showError("Bitte mindestens einen Wert eingeben.");
      $("ks_meta").textContent = "Noch keine Auswertung.";
      return;
    }

    const mode = $("ksMode").value;
    const stepG = C.num($("ksGradeStep").value);
    const bestG = C.num($("ksBestG").value);
    const worstG = C.num($("ksWorstG").value);
    const maxPts = C.num($("ksMaxPts").value);
    const bestFrom = C.num($("ksBestFrom").value);
    const worstFrom = C.num($("ksWorstFrom").value);
    const passThr = C.num($("ksPassThreshold").value);

    if (![stepG,bestG,worstG].every(C.isFiniteNum)) { showError("Skala ungültig."); return; }
    if (stepG <= 0 || bestG === worstG)             { showError("Skala ungültig."); return; }

    let grades, points = null;
    let halfPts = false;
    if (mode === "points") {
      if (![maxPts,bestFrom,worstFrom].every(C.isFiniteNum)) { showError("Punkte-Skala ungültig."); return; }
      // halfPts aus dem geteilten Notenschlüssel übernehmen, sonst false (ganze Punkte).
      if ($("ksUseKey").value === "yes") {
        const k = S.getSharedKey();
        halfPts = !!(k && k.halfPts === "yes");
      }
      points = raw;
      grades = points.map(p => pointsToGrade(p, {
        bestG, worstG, maxPts, bestFrom, worstFrom, halfPts, stepG
      }));
    } else {
      grades = raw;
    }

    const stats = statistics(grades);
    const stP = (mode === "points") ? statistics(points) : null;
    const dist = distribution(grades, stepG, bestG, worstG);

    // Modus aus der gerundeten Verteilung holen, damit er zum Noten-Schritt passt
    // (der "rohe" stats.mode kann z.B. 1,5 sein während stepG=1 vergeben wird).
    let modeFromDist = NaN;
    if (dist.length > 0) {
      let maxC = 0;
      for (const d of dist) if (d.count > maxC) { maxC = d.count; modeFromDist = d.grade; }
    }
    // "Beste/Schlechteste im Datensatz" hängt von der Skalen-Richtung ab.
    const higherIsBetter = bestG > worstG;
    const bestInData  = higherIsBetter ? stats.max : stats.min;
    const worstInData = higherIsBetter ? stats.min : stats.max;

    // Bestehensquote — gezählt wird die auf den Notenschritt gerundete Note,
    // damit das Ergebnis zur Verteilungstabelle passt (eine 4,4 erscheint dort
    // bei ganzen Noten als 4 und gilt dann auch hier als bestanden).
    const loG = Math.min(bestG, worstG);
    const hiG = Math.max(bestG, worstG);
    let passed = 0;
    if (C.isFiniteNum(passThr)) {
      for (const g of grades) {
        const r = C.clampMinMax(C.roundToStep(g, stepG), loG, hiG);
        if (higherIsBetter) { if (r >= passThr - 1e-9) passed++; }
        else                { if (r <= passThr + 1e-9) passed++; }
      }
    }
    const passRate = grades.length ? (passed / grades.length) : 0;

    // Summary-Tabelle
    const sb = $("ks_summary_body");
    const fmtG = (x) => C.fmtGrade(x, stepG);
    // Kennzahlen aus Rohwerten (Durchschnitt, Median, Beste/Schlechteste) immer
    // mit mindestens einer Nachkommastelle anzeigen — bei Schritt "ganze Noten"
    // würde der Klassenschnitt sonst ohne Dezimalstelle erscheinen (2 statt 2,4).
    const statDecimals = Math.max(1, C.decimalsForStep(stepG));
    const fmtStat = (x) => Number(x).toFixed(statDecimals).replace(".", ",");
    const rowsSummary = [
      ["Anzahl Werte", String(stats.n)],
      ["Durchschnitt (Note)", fmtStat(stats.mean)],
      ["Median", fmtStat(stats.median)],
      ["Modus (häufigster Wert)", Number.isFinite(modeFromDist) ? fmtG(modeFromDist) : "–"],
      ["Beste Note (im Datensatz)", fmtStat(bestInData)],
      ["Schlechteste Note (im Datensatz)", fmtStat(worstInData)],
      ["Spannweite", C.fmt(stats.range, 0.1)],
      ["Standardabweichung", C.fmt(stats.stdev, 0.1)],
      // "ab"/"bis" statt "≥"/"≤": liest sich natürlicher, passt zum Feld-Label
      // "Bestanden bis Note" und ist im PDF darstellbar (≤/≥ sind kein WinAnsi).
      ["Bestanden (" + (higherIsBetter ? "ab " : "bis ") + C.fmt(passThr, 0.1) + ")", passed + " von " + grades.length + " (" + (passRate * 100).toFixed(1).replace(".", ",") + " %)"]
    ];
    if (mode === "points" && stP) {
      rowsSummary.splice(2, 0,
        ["Durchschnitt (Punkte)", C.fmt(stP.mean, 0.1)],
        ["Min/Max Punkte", C.fmt(stP.min, 0.1) + " / " + C.fmt(stP.max, 0.1)]
      );
    }
    for (const r of rowsSummary) {
      const tr = document.createElement("tr");
      const td1 = document.createElement("td"); td1.textContent = r[0];
      const td2 = document.createElement("td"); td2.className = "num"; td2.textContent = r[1];
      const td3 = document.createElement("td"); td3.className = "col-filler"; td3.setAttribute("aria-hidden", "true");
      tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
      sb.appendChild(tr);
    }
    $("ks_summary").hidden = false;

    // Verteilung
    const db = $("ks_dist_body");
    for (const d of dist) {
      const tr = document.createElement("tr");
      const td1 = document.createElement("td"); td1.className = "num"; td1.textContent = fmtG(d.grade);
      const td2 = document.createElement("td"); td2.className = "num"; td2.textContent = String(d.count);
      const td3 = document.createElement("td"); td3.className = "num"; td3.textContent = (d.share * 100).toFixed(1).replace(".", ",") + " %";
      const td4 = document.createElement("td"); td4.className = "col-filler"; td4.setAttribute("aria-hidden", "true");
      tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3); tr.appendChild(td4);
      db.appendChild(tr);
    }
    $("ks_dist").hidden = false;

    $("ks_meta").textContent = "Modus: " + (mode === "points" ? "Punkte → Note" : "Noten") +
                               " · Schritt " + C.fmtGrade(stepG, 0.1) +
                               " · Skala " + C.fmtGrade(bestG, 0.1) + "–" + C.fmtGrade(worstG, 0.1);

    lastResult = { mode, raw, grades, stats, stP, dist, passed, passRate, passThr,
                   stepG, bestG, worstG, maxPts, bestFrom, worstFrom, summary: rowsSummary };
    $("ks_csv").disabled = false; $("ks_csv").setAttribute("aria-disabled", "false");
    $("ks_pdf").disabled = false; $("ks_pdf").setAttribute("aria-disabled", "false");
  }

  // ── CSV ─────────────────────────────────────────────────────
  function exportCSV() {
    if (!lastResult) return;
    const r = lastResult;
    const rows = [["Klausur-Statistik"], []];
    for (const s of r.summary) rows.push(s);
    rows.push([], ["Verteilung"]);
    rows.push(["Note", "Anzahl", "Anteil"]);
    for (const d of r.dist) {
      rows.push([
        C.fmtGrade(d.grade, r.stepG),
        String(d.count),
        (d.share * 100).toFixed(1).replace(".", ",") + " %"
      ]);
    }
    rows.push([], ["Werte"]);
    rows.push([(r.mode === "points") ? "Punkte" : "Noten"]);
    for (const v of r.raw) rows.push([String(v).replace(".", ",")]);
    E.exportCSV(rows, "klausur-statistik");
  }

  // ── PDF ─────────────────────────────────────────────────────
  function exportPDF() {
    if (!lastResult) return;
    const r = lastResult;
    E.exportPDF("klausur-statistik", (w) => {
      w.line("Klausur-Statistik", 16, true);
      w.line("Modus: " + (r.mode === "points" ? "Punkte zu Note" : "Noten"), 11);
      w.line("Skala: " + C.fmtGrade(r.bestG, 0.1) + "–" + C.fmtGrade(r.worstG, 0.1) +
             " · Schritt " + C.fmtGrade(r.stepG, 0.1), 11);
      w.blank(8);
      w.line("Kennzahlen", 12, true);
      // Spaltenbreite Kennzahl muss "Schlechteste Note (im Datensatz)" fassen.
      w.table(["Kennzahl", "Wert"], r.summary, [0.50, 0.20]);
      w.blank(8);
      w.line("Verteilung", 12, true);
      const distRows = r.dist.map(d => [
        C.fmtGrade(d.grade, r.stepG),
        String(d.count),
        (d.share * 100).toFixed(1).replace(".", ",") + " %"
      ]);
      w.table(["Note", "Anzahl", "Anteil"], distRows, [0.10, 0.12, 0.14]);
    });
  }

  // ── Reset ───────────────────────────────────────────────────
  function reset() {
    $("ksValues").value = "";
    $("ksMode").value = "grades";
    $("ksGradeStep").value = "1";
    $("ksUseKey").value = "yes";
    $("ksPassThreshold").value = "4";
    $("ksBestG").value = "1";
    $("ksWorstG").value = "6";
    $("ksMaxPts").value = "60";
    $("ksBestFrom").value = "60";
    $("ksWorstFrom").value = "0";
    showError("");
    $("ks_summary").hidden = true;
    $("ks_dist").hidden = true;
    $("ks_meta").textContent = "Noch keine Auswertung.";
    $("ks_csv").disabled = true; $("ks_csv").setAttribute("aria-disabled", "true");
    $("ks_pdf").disabled = true; $("ks_pdf").setAttribute("aria-disabled", "true");
    lastResult = null;
    toggleOwnFields();
    applyStoredKey();
    urlUpdate();
  }

  function init() {
    defaults = S.captureDefaults(SCHEMA);
    urlUpdate = S.makeUrlUpdater(SCHEMA, defaults);

    const params = S.applyFromQueryString();
    if ([...params.keys()].length) S.applyFromParams(SCHEMA, params);

    toggleOwnFields();
    applyStoredKey();

    const form = $("ks_form");
    form.addEventListener("input", () => urlUpdate());
    form.addEventListener("change", () => {
      toggleOwnFields();
      applyStoredKey();
      urlUpdate();
    });
    form.addEventListener("submit", (e) => { e.preventDefault(); build(); });
    form.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && e.target.tagName !== "TEXTAREA") {
        e.preventDefault(); build();
      }
    });

    $("ks_calc").addEventListener("click", build);
    $("ks_reset").addEventListener("click", reset);
    $("ks_csv").addEventListener("click", exportCSV);
    $("ks_pdf").addEventListener("click", exportPDF);

    const snaps = S.snapshots(TOOL);
    const listEl = $("ks_snap_list");
    const flashEl = $("ks_flash");
    const flash = (m) => {
      flashEl.textContent = m; flashEl.style.opacity = "1";
      clearTimeout(flashEl._t);
      flashEl._t = setTimeout(() => { flashEl.style.opacity = "0"; }, 1800);
    };
    function rerender() {
      S.renderSnapshotList(listEl, snaps, SCHEMA, (act) => {
        if (act === "apply") { toggleOwnFields(); }
        rerender();
      });
    }
    rerender();

    $("ks_permalink").addEventListener("click", () => S.copyPermalink(SCHEMA, defaults, flashEl));
    $("ks_snap_add").addEventListener("click", () => {
      const name = prompt("Name für die Konfiguration:", "Statistik " + new Date().toLocaleDateString("de-DE"));
      if (name === null) return;
      snaps.add(name, SCHEMA, defaults);
      rerender();
      flash("Schnappschuss gespeichert");
    });
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
