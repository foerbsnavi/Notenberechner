/* ─────────────────────────────────────────────────────────────
   punkte-note.js — Tool 2: Punkte → Note (Einzelrechner).
   ───────────────────────────────────────────────────────────── */

(function () {
  "use strict";
  if (!window.NbCore || !window.NbState || !window.NbExport) return;

  const C = window.NbCore;
  const S = window.NbState;
  const E = window.NbExport;
  const $ = (id) => document.getElementById(id);

  const TOOL = "punkte-note";
  const SCHEMA = {
    fields: ["pnPoints","pnHalfPts","pnUseKey","pnGradeStep",
             "pnBestG","pnWorstG","pnMaxPts","pnBestFrom","pnWorstFrom"],
    radios: []
  };

  let defaults = null;
  let urlUpdate = function () {};
  let lastResult = null;

  // ── Schlüssel-Sync: gespeicherten Schlüssel ggf. übernehmen ─
  function refreshKeyHint() {
    const hint = $("pn_key_hint");
    if (!hint) return;
    if ($("pnUseKey").value !== "yes") { hint.textContent = ""; return; }
    const k = S.getSharedKey();
    if (k) {
      hint.textContent = "Aktueller Schlüssel: Skala " + k.bestG + "–" + k.worstG +
                         ", max " + k.maxPts + " Pkt.";
    } else {
      hint.textContent = 'Noch kein Schlüssel im "Notenschlüssel"-Tool angelegt — eigene Werte aktiv.';
    }
  }

  function applyStoredKey() {
    if ($("pnUseKey").value !== "yes") { refreshKeyHint(); return; }
    const k = S.getSharedKey();
    if (!k) {
      // Kein Schlüssel da → automatisch auf "eigene Werte" wechseln
      $("pnUseKey").value = "no";
      refreshKeyHint();
      return;
    }
    if (k.bestG     !== undefined) $("pnBestG").value     = k.bestG;
    if (k.worstG    !== undefined) $("pnWorstG").value    = k.worstG;
    if (k.maxPts    !== undefined) $("pnMaxPts").value    = k.maxPts;
    if (k.bestFrom  !== undefined) $("pnBestFrom").value  = k.bestFrom;
    if (k.worstFrom !== undefined) $("pnWorstFrom").value = k.worstFrom;
    if (k.halfPts   !== undefined) $("pnHalfPts").value   = k.halfPts;
    if (k.gradeStep !== undefined) $("pnGradeStep").value = k.gradeStep;
    refreshKeyHint();
  }

  function toggleOwnFields() {
    const own = $("pnUseKey").value === "no";
    ["pnBestG","pnWorstG","pnMaxPts","pnBestFrom","pnWorstFrom","pnGradeStep","pnHalfPts"].forEach(id => {
      $(id).disabled = !own;
    });
  }

  function showError(msg) { $("pn_err").textContent = msg || ""; }

  // ── Berechnung ──────────────────────────────────────────────
  function calc() {
    showError("");

    const halfPts = $("pnHalfPts").value === "yes";
    const stepP = halfPts ? 0.5 : 1;

    const pIn       = C.num($("pnPoints").value);
    const bestG     = C.num($("pnBestG").value);
    const worstG    = C.num($("pnWorstG").value);
    const maxPts    = C.num($("pnMaxPts").value);
    const bestFrom  = C.num($("pnBestFrom").value);
    const worstFrom = C.num($("pnWorstFrom").value);
    const stepG     = C.num($("pnGradeStep").value);

    if (!C.isFiniteNum(pIn))                                       { showError("Bitte Punkte als Zahl eingeben."); return; }
    if (![bestG,worstG,maxPts,bestFrom,worstFrom,stepG].every(C.isFiniteNum)) { showError("Skala ungültig."); return; }
    if (stepG <= 0)         { showError("Notenschritt muss > 0 sein."); return; }
    if (maxPts <= 0)        { showError("Max. Punkte müssen größer als 0 sein."); return; }
    if (bestG === worstG)   { showError("Beste und schlechteste Note dürfen nicht gleich sein."); return; }

    const stepsMax = Math.floor((maxPts + 1e-9) / stepP);
    const maxGrid  = stepsMax * stepP;
    const bF = C.clamp(bestFrom,  0, maxGrid);
    const wF = C.clamp(worstFrom, 0, maxGrid);
    if (bF <= wF) { showError('"Beste ab Punkte" muss größer sein als "Schlechteste ab Punkte".'); return; }

    let p = C.clamp(pIn, 0, maxGrid);
    p = Math.round(p / stepP) * stepP;

    let g = C.linearGradeFromPoints(p, { bestG, worstG, bestFrom: bF, worstFrom: wF });
    g = C.clampMinMax(C.roundToStep(g, stepG), bestG, worstG);

    $("pn_grade").textContent = C.fmtGrade(g, stepG);
    const source = ($("pnUseKey").value === "yes") ? "gespeicherter Schlüssel" : "eigene Werte";
    $("pn_meta").textContent = "Punkte " + C.fmtPts(p, halfPts) + " / " + C.fmtPts(maxGrid, halfPts) +
                               " · Schritt " + C.fmtGrade(stepG, 0.1) +
                               " · " + source;

    lastResult = { points: p, maxGrid, grade: g, halfPts, stepG, bestG, worstG, bestFrom: bF, worstFrom: wF, source };
    $("pn_pdf").disabled = false;
    $("pn_pdf").setAttribute("aria-disabled", "false");
  }

  // ── PDF ─────────────────────────────────────────────────────
  function exportPDF() {
    if (!lastResult) return;
    const r = lastResult;
    E.exportPDF("punkte-note", (w) => {
      w.line("Punkte → Note", 16, true);
      w.line("Punkte: " + C.fmtPts(r.points, r.halfPts) + " von " + C.fmtPts(r.maxGrid, r.halfPts), 11);
      w.line("Skala: " + C.fmtGrade(r.bestG, 0.1) + " (best) bis " + C.fmtGrade(r.worstG, 0.1) + " (schlecht)", 11);
      w.line("Notenschritt: " + C.fmtGrade(r.stepG, 0.1), 11);
      w.line("Beste-Note ab: " + C.fmtPts(r.bestFrom, r.halfPts) + " · Schlechteste-Note ab: " + C.fmtPts(r.worstFrom, r.halfPts), 11);
      w.line("Quelle: " + r.source, 10);
      w.blank(8);
      w.line("Note: " + C.fmtGrade(r.grade, r.stepG), 22, true);
    });
  }

  // ── Reset ───────────────────────────────────────────────────
  function reset() {
    $("pnPoints").value     = "0";
    $("pnHalfPts").value    = "yes";
    $("pnUseKey").value     = "yes";
    $("pnGradeStep").value  = "1";
    $("pnBestG").value      = "1";
    $("pnWorstG").value     = "6";
    $("pnMaxPts").value     = "60";
    $("pnBestFrom").value   = "60";
    $("pnWorstFrom").value  = "0";
    showError("");
    $("pn_grade").textContent = "–";
    $("pn_meta").textContent = "Noch keine Berechnung.";
    lastResult = null;
    $("pn_pdf").disabled = true;
    $("pn_pdf").setAttribute("aria-disabled", "true");
    toggleOwnFields();
    applyStoredKey();
    urlUpdate();
    calc();
  }

  // ── Init ────────────────────────────────────────────────────
  function init() {
    defaults = S.captureDefaults(SCHEMA);
    urlUpdate = S.makeUrlUpdater(SCHEMA, defaults);

    const params = S.applyFromQueryString();
    if ([...params.keys()].length) S.applyFromParams(SCHEMA, params);

    toggleOwnFields();
    applyStoredKey();

    // Live-Modus mit 150 ms Debounce
    const debouncedCalc = C.debounce(() => { urlUpdate(); calc(); }, 150);

    const form = $("pn_form");
    form.addEventListener("input", debouncedCalc);
    form.addEventListener("change", () => {
      toggleOwnFields();
      applyStoredKey();
      urlUpdate();
      calc();
    });
    form.addEventListener("submit", (e) => { e.preventDefault(); calc(); });
    form.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && e.target.tagName !== "TEXTAREA") {
        e.preventDefault(); calc();
      }
    });

    $("pn_calc").addEventListener("click", calc);
    $("pn_reset").addEventListener("click", reset);
    $("pn_pdf").addEventListener("click", exportPDF);

    // maxPts → bestFrom auto-sync (nur wenn eigene Werte aktiv)
    $("pnMaxPts").addEventListener("input", () => {
      if ($("pnUseKey").value === "no") {
        const m = C.num($("pnMaxPts").value);
        if (C.isFiniteNum(m)) $("pnBestFrom").value = m;
      }
    });

    // Snapshots
    const snaps = S.snapshots(TOOL);
    const listEl = $("pn_snap_list");
    const flashEl = $("pn_flash");
    const flash = (m) => {
      flashEl.textContent = m; flashEl.style.opacity = "1";
      clearTimeout(flashEl._t);
      flashEl._t = setTimeout(() => { flashEl.style.opacity = "0"; }, 1800);
    };
    function rerender() {
      S.renderSnapshotList(listEl, snaps, SCHEMA, (act) => {
        if (act === "apply") { toggleOwnFields(); calc(); }
        rerender();
      });
    }
    rerender();

    $("pn_permalink").addEventListener("click", () => S.copyPermalink(SCHEMA, defaults, flashEl));
    $("pn_snap_add").addEventListener("click", () => {
      const name = prompt("Name für die Konfiguration:", "Punkte→Note " + new Date().toLocaleDateString("de-DE"));
      if (name === null) return;
      snaps.add(name, SCHEMA, defaults);
      rerender();
      flash("Schnappschuss gespeichert");
    });

    calc();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
