/* ─────────────────────────────────────────────────────────────
   note-punkte.js — Tool 3: Note → Punkte (Rückwärtsrechner).
   ───────────────────────────────────────────────────────────── */

(function () {
  "use strict";
  if (!window.NbCore || !window.NbState || !window.NbExport) return;

  const C = window.NbCore;
  const S = window.NbState;
  const E = window.NbExport;
  const $ = (id) => document.getElementById(id);

  const TOOL = "note-punkte";
  const SCHEMA = {
    fields: ["npGrade","npHalfPts","npUseKey","npGradeStep",
             "npBestG","npWorstG","npMaxPts","npBestFrom","npWorstFrom"],
    radios: []
  };

  let defaults = null;
  let urlUpdate = function () {};
  let lastResult = null;

  function refreshKeyHint() {
    const hint = $("np_key_hint");
    if (!hint) return;
    if ($("npUseKey").value !== "yes") { hint.textContent = ""; return; }
    const k = S.getSharedKey();
    if (k) {
      hint.textContent = "Aktueller Schlüssel: Skala " + k.bestG + "–" + k.worstG +
                         ", max " + k.maxPts + " Pkt.";
    } else {
      hint.textContent = 'Noch kein Schlüssel im "Notenschlüssel"-Tool angelegt — eigene Werte aktiv.';
    }
  }

  function applyStoredKey() {
    if ($("npUseKey").value !== "yes") { refreshKeyHint(); return; }
    const k = S.getSharedKey();
    if (!k) {
      $("npUseKey").value = "no";
      refreshKeyHint();
      return;
    }
    if (k.bestG     !== undefined) $("npBestG").value     = k.bestG;
    if (k.worstG    !== undefined) $("npWorstG").value    = k.worstG;
    if (k.maxPts    !== undefined) $("npMaxPts").value    = k.maxPts;
    if (k.bestFrom  !== undefined) $("npBestFrom").value  = k.bestFrom;
    if (k.worstFrom !== undefined) $("npWorstFrom").value = k.worstFrom;
    if (k.halfPts   !== undefined) $("npHalfPts").value   = k.halfPts;
    if (k.gradeStep !== undefined) $("npGradeStep").value = k.gradeStep;
    refreshKeyHint();
  }

  function toggleOwnFields() {
    const own = $("npUseKey").value === "no";
    ["npBestG","npWorstG","npMaxPts","npBestFrom","npWorstFrom","npGradeStep","npHalfPts"].forEach(id => {
      $(id).disabled = !own;
    });
  }

  function showError(msg) { $("np_err").textContent = msg || ""; }

  function calc() {
    showError("");

    const halfPts = $("npHalfPts").value === "yes";
    const stepP = halfPts ? 0.5 : 1;

    const targetIn  = C.num($("npGrade").value);
    const bestG     = C.num($("npBestG").value);
    const worstG    = C.num($("npWorstG").value);
    const maxPts    = C.num($("npMaxPts").value);
    const bestFrom  = C.num($("npBestFrom").value);
    const worstFrom = C.num($("npWorstFrom").value);
    const stepG     = C.num($("npGradeStep").value);

    if (!C.isFiniteNum(targetIn)) { showError("Bitte Ziel-Note als Zahl eingeben."); return; }
    if (![bestG,worstG,maxPts,bestFrom,worstFrom,stepG].every(C.isFiniteNum)) { showError("Skala ungültig."); return; }
    if (stepG <= 0)        { showError("Notenschritt muss > 0 sein."); return; }
    if (maxPts <= 0)       { showError("Max. Punkte müssen größer als 0 sein."); return; }
    if (bestG === worstG)  { showError("Beste und schlechteste Note dürfen nicht gleich sein."); return; }

    // Ziel-Note außerhalb der Skala? Klar melden statt stillschweigend zu klemmen.
    const lo = Math.min(bestG, worstG);
    const hi = Math.max(bestG, worstG);
    if (targetIn < lo - 1e-9 || targetIn > hi + 1e-9) {
      showError("Ziel-Note " + C.fmtGrade(targetIn, stepG) +
                " liegt außerhalb der Skala " + C.fmtGrade(lo, 0.1) + "–" + C.fmtGrade(hi, 0.1) + ".");
      return;
    }

    const stepsMax = Math.floor((maxPts + 1e-9) / stepP);
    const maxGrid  = stepsMax * stepP;
    const bF = C.clamp(bestFrom,  0, maxGrid);
    const wF = C.clamp(worstFrom, 0, maxGrid);
    if (bF <= wF) { showError('"Beste ab Punkte" muss größer sein als "Schlechteste ab Punkte".'); return; }

    let target = C.clampMinMax(C.roundToStep(targetIn, stepG), bestG, worstG);

    let found = null;
    for (let i = 0; i <= stepsMax; i++) {
      const pp = i * stepP;
      let g = C.linearGradeFromPoints(pp, { bestG, worstG, bestFrom: bF, worstFrom: wF });
      g = C.clampMinMax(C.roundToStep(g, stepG), bestG, worstG);
      if (C.gradeMeetsTarget(g, target, bestG, worstG)) { found = pp; break; }
    }

    if (found === null) {
      showError("Mit dieser Skala nicht erreichbar.");
      $("np_pts").textContent = "–";
      $("np_hint").textContent = "";
      lastResult = null;
      $("np_pdf").disabled = true;
      $("np_pdf").setAttribute("aria-disabled", "true");
      return;
    }

    let checkG = C.linearGradeFromPoints(found, { bestG, worstG, bestFrom: bF, worstFrom: wF });
    checkG = C.clampMinMax(C.roundToStep(checkG, stepG), bestG, worstG);

    $("np_pts").textContent = C.fmtPts(found, halfPts);
    const source = ($("npUseKey").value === "yes") ? "gespeicherter Schlüssel" : "eigene Werte";
    $("np_meta").textContent = "Ziel " + C.fmtGrade(target, stepG) +
                               " · Schritt " + C.fmtGrade(stepG, 0.1) +
                               " · " + source;
    $("np_hint").textContent = "Prüfung: " + C.fmtPts(found, halfPts) + " / " +
                               C.fmtPts(maxGrid, halfPts) + " ⇒ Note " + C.fmtGrade(checkG, stepG);

    lastResult = { target, found, checkG, maxGrid, halfPts, stepG, bestG, worstG, bestFrom: bF, worstFrom: wF, source };
    $("np_pdf").disabled = false;
    $("np_pdf").setAttribute("aria-disabled", "false");
  }

  function exportPDF() {
    if (!lastResult) return;
    const r = lastResult;
    E.exportPDF("note-punkte", (w) => {
      // Kein "→" — jsPDF-Helvetica kann kein U+2192 (pdfSafe würde "->" draus machen).
      w.line("Note zu Punkte", 16, true);
      w.line("Ziel-Note: " + C.fmtGrade(r.target, r.stepG), 11);
      w.line("Skala: " + C.fmtGrade(r.bestG, 0.1) + " (best) bis " + C.fmtGrade(r.worstG, 0.1) + " (schlecht)", 11);
      w.line("Max. Punkte: " + C.fmtPts(r.maxGrid, r.halfPts), 11);
      w.line("Notenschritt: " + C.fmtGrade(r.stepG, 0.1), 11);
      w.line("Quelle: " + r.source, 10);
      w.blank(8);
      w.line("Mindestens benötigt: " + C.fmtPts(r.found, r.halfPts) + " Punkte", 18, true);
      w.line("(ergibt Note " + C.fmtGrade(r.checkG, r.stepG) + ")", 11);
    });
  }

  function reset() {
    $("npGrade").value      = "2";
    $("npHalfPts").value    = "yes";
    $("npUseKey").value     = "yes";
    $("npGradeStep").value  = "1";
    $("npBestG").value      = "1";
    $("npWorstG").value     = "6";
    $("npMaxPts").value     = "60";
    $("npBestFrom").value   = "60";
    $("npWorstFrom").value  = "0";
    showError("");
    $("np_pts").textContent = "–";
    $("np_meta").textContent = "Noch keine Berechnung.";
    $("np_hint").textContent = "";
    lastResult = null;
    $("np_pdf").disabled = true;
    $("np_pdf").setAttribute("aria-disabled", "true");
    toggleOwnFields();
    applyStoredKey();
    urlUpdate();
    calc();
  }

  function init() {
    defaults = S.captureDefaults(SCHEMA);
    urlUpdate = S.makeUrlUpdater(SCHEMA, defaults);

    const params = S.applyFromQueryString();
    if ([...params.keys()].length) S.applyFromParams(SCHEMA, params);

    toggleOwnFields();
    applyStoredKey();

    const debouncedCalc = C.debounce(() => { urlUpdate(); calc(); }, 150);

    const form = $("np_form");
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

    $("np_calc").addEventListener("click", calc);
    $("np_reset").addEventListener("click", reset);
    $("np_pdf").addEventListener("click", exportPDF);

    $("npMaxPts").addEventListener("input", () => {
      if ($("npUseKey").value === "no") {
        const m = C.num($("npMaxPts").value);
        if (C.isFiniteNum(m)) $("npBestFrom").value = m;
      }
    });

    const snaps = S.snapshots(TOOL);
    const listEl = $("np_snap_list");
    const flashEl = $("np_flash");
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

    $("np_permalink").addEventListener("click", () => S.copyPermalink(SCHEMA, defaults, flashEl));
    $("np_snap_add").addEventListener("click", () => {
      const name = prompt("Name für die Konfiguration:", "Note→Punkte " + new Date().toLocaleDateString("de-DE"));
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
