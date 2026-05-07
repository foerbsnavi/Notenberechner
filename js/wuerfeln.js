/* ─────────────────────────────────────────────────────────────
   wuerfeln.js — Tool 7: Note würfeln.
   ───────────────────────────────────────────────────────────── */

(function () {
  "use strict";
  if (!window.NbCore || !window.NbExport) return;

  const C = window.NbCore;
  const E = window.NbExport;
  const $ = (id) => document.getElementById(id);

  let lastResult = null;

  function biasedRand(bias) {
    const r = Math.random();
    if (bias === "good") return Math.pow(r, 2.2);
    if (bias === "bad")  return 1 - Math.pow(r, 2.2);
    return r;
  }

  function showError(msg) { $("dc_err").textContent = msg || ""; }

  function biasLabel(b) {
    if (b === "good") return "Eher gut";
    if (b === "bad")  return "Eher nicht so gut";
    return "Zufall";
  }

  function roll() {
    showError("");
    const step = C.num($("dcFormat").value);
    let minG = C.num($("dcMin").value);
    let maxG = C.num($("dcMax").value);
    const bias = $("dcBias").value;

    if (!C.isFiniteNum(step) || step <= 0) { showError("Notenformat ungültig."); return; }
    if (!C.isFiniteNum(minG) || !C.isFiniteNum(maxG)) {
      showError("Min/Max bitte als Zahl."); return;
    }
    if (minG > maxG) { const t = minG; minG = maxG; maxG = t; }

    // Tendenz-Klemmen: wenn Bereich danach leer wird, klare Meldung.
    let clampedNote = "";
    if (bias === "good" && maxG > 3) {
      if (minG > 3) {
        showError('Bei Tendenz "Eher gut" muss der Bereich Note 3 oder besser einschließen.');
        return;
      }
      maxG = 3;
      clampedNote = ' (Bereich auf 3 begrenzt wegen Tendenz "Eher gut")';
    }
    if (bias === "bad" && minG < 3) {
      if (maxG < 3) {
        showError('Bei Tendenz "Eher nicht so gut" muss der Bereich Note 3 oder schlechter einschließen.');
        return;
      }
      minG = 3;
      clampedNote = ' (Bereich auf 3 begrenzt wegen Tendenz "Eher nicht so gut")';
    }

    const span = maxG - minG;
    if (span < 0) { showError("Bereich ungültig."); return; }

    let g;
    if (span === 0) {
      g = minG;
    } else {
      g = minG + biasedRand(bias) * span;
      g = C.roundToStep(g, step);
      g = C.clamp(g, minG, maxG);
    }

    $("dc_out").textContent = C.fmt(g, step);
    const fmtLabel = $("dcFormat").options[$("dcFormat").selectedIndex].text;
    $("dc_meta").textContent =
      "Format: " + fmtLabel +
      " · Tendenz: " + biasLabel(bias) +
      " · Bereich: " + C.fmt(minG, 0.1) + "–" + C.fmt(maxG, 0.1) + clampedNote;

    lastResult = { grade: g, step, minG, maxG, bias, fmtLabel };
    $("dc_pdf").disabled = false;
    $("dc_pdf").setAttribute("aria-disabled", "false");
  }

  function exportPDF() {
    if (!lastResult) return;
    const r = lastResult;
    E.exportPDF("note-wuerfeln", (w) => {
      w.line("Note würfeln — Ergebnis", 16, true);
      w.line("Format: " + r.fmtLabel, 11);
      w.line("Tendenz: " + biasLabel(r.bias), 11);
      w.line("Bereich: " + C.fmt(r.minG, 0.1) + "–" + C.fmt(r.maxG, 0.1), 11);
      w.blank(8);
      w.line("Note: " + C.fmt(r.grade, r.step), 22, true);
    });
  }

  function reset() {
    $("dcFormat").value = "0.1";
    $("dcBias").value = "random";
    $("dcMin").value = "1";
    $("dcMax").value = "6";
    $("dc_out").textContent = "–";
    $("dc_meta").textContent = "Noch nicht gewürfelt.";
    showError("");
    lastResult = null;
    $("dc_pdf").disabled = true;
    $("dc_pdf").setAttribute("aria-disabled", "true");
  }

  function init() {
    $("dc_roll").addEventListener("click", roll);
    $("dc_reset").addEventListener("click", reset);
    $("dc_pdf").addEventListener("click", exportPDF);

    const form = $("dc_form");
    form.addEventListener("submit", (e) => { e.preventDefault(); roll(); });
    form.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && e.target.tagName !== "TEXTAREA") {
        e.preventDefault(); roll();
      }
    });
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
