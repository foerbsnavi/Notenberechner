/* ─────────────────────────────────────────────────────────────
   blocknoten.js — Tool 5: Blocknoten gewichtet zusammenrechnen.
   ───────────────────────────────────────────────────────────── */

(function () {
  "use strict";
  if (!window.NbCore || !window.NbState || !window.NbExport) return;

  const C = window.NbCore;
  const S = window.NbState;
  const E = window.NbExport;
  const $ = (id) => document.getElementById(id);
  const NOTES_PER_BLOCK = 5;

  const TOOL = "blocknoten";
  // Schema dynamisch — Felder werden aus dem aktuellen Block-State erzeugt
  function buildSchema(blockIds) {
    const fields = ["bnMode"];
    for (const id of blockIds) {
      fields.push("bn_name_" + id, "bn_weight_" + id);
      for (let i = 1; i <= NOTES_PER_BLOCK; i++) fields.push("bn_n_" + id + "_" + i);
    }
    return { fields, radios: [] };
  }

  let blockIds = [1];
  let nextId   = 2;
  let lastResult = null;
  let urlUpdate = function () {};
  let defaults  = null;

  // ── Block-Rendering ─────────────────────────────────────────
  function el(tag, attrs, children) {
    const e = document.createElement(tag);
    if (attrs) for (const k in attrs) {
      if (k === "class")     e.className = attrs[k];
      else if (k === "text") e.textContent = attrs[k];
      else                   e.setAttribute(k, attrs[k]);
    }
    if (children) for (const c of children) if (c) e.appendChild(c);
    return e;
  }

  function inputField(labelText, inputAttrs, fieldClass) {
    const id = inputAttrs.id;
    const lbl = el("label", { for: id, text: labelText });
    const inp = el("input", inputAttrs);
    return el("div", { class: "field" + (fieldClass ? " " + fieldClass : "") }, [lbl, inp]);
  }

  function blockEl(id) {
    const wrap = el("div", { class: "block-card", id: "bn_block_" + id });

    if (id !== 1) {
      const rm = el("button", {
        type: "button", class: "block-remove",
        "aria-label": "Block " + id + " entfernen", text: "×"
      });
      rm.addEventListener("click", () => removeBlock(id));
      wrap.appendChild(rm);
    }

    wrap.appendChild(inputField("Name", {
      id: "bn_name_" + id, type: "text", maxlength: "40", placeholder: "z.B. Klassenarbeit"
    }));
    wrap.appendChild(inputField("Gewicht", {
      id: "bn_weight_" + id, type: "number", step: "0.1", min: "0",
      inputmode: "decimal", placeholder: "z.B. 50"
    }));

    // Note 1–5 horizontal, kompakt
    const notesWrap = el("div", { class: "block-notes" });
    const notesLabel = el("span", { class: "block-notes-label", text: "Noten 1–" + NOTES_PER_BLOCK });
    notesWrap.appendChild(notesLabel);
    const grid = el("div", { class: "block-notes-grid" });
    for (let i = 1; i <= NOTES_PER_BLOCK; i++) {
      const inpId = "bn_n_" + id + "_" + i;
      const lbl = el("label", { for: inpId, class: "sr-only", text: "Note " + i });
      const inp = el("input", {
        id: inpId, type: "number", step: "0.1", min: "0", inputmode: "decimal",
        "aria-label": "Note " + i, placeholder: String(i)
      });
      grid.appendChild(lbl);
      grid.appendChild(inp);
    }
    notesWrap.appendChild(grid);
    wrap.appendChild(notesWrap);

    const result = el("div", {
      class: "block-result", id: "bn_result_" + id,
      text: "Ungewichtet: 0,00 · Gewichtet: 0,00"
    });
    wrap.appendChild(result);

    return wrap;
  }

  function renderBlocks() {
    const wrap = $("bn_blocks");
    wrap.innerHTML = "";
    for (const id of blockIds) wrap.appendChild(blockEl(id));
    updateWeightSum();
  }

  function addBlock() {
    blockIds.push(nextId++);
    renderBlocks();
    urlUpdate();
  }

  function removeBlock(id) {
    const idx = blockIds.indexOf(id);
    if (idx >= 0) blockIds.splice(idx, 1);
    if (blockIds.length === 0) blockIds.push(1);
    renderBlocks();
    if (lastResult) calc();
    urlUpdate();
  }

  // ── Auswertung ──────────────────────────────────────────────
  function readBlock(id) {
    const w = C.num(($("bn_weight_" + id) || {}).value);
    const name = (($("bn_name_" + id) || {}).value || "").trim() || ("Block " + id);
    let sum = 0, n = 0;
    for (let i = 1; i <= NOTES_PER_BLOCK; i++) {
      const v = C.num(($("bn_n_" + id + "_" + i) || {}).value);
      if (C.isFiniteNum(v)) { sum += v; n++; }
    }
    return { id, name, weight: C.isFiniteNum(w) && w > 0 ? w : 0, n, ungewichtet: n ? sum / n : 0 };
  }

  function updateWeightSum() {
    let s = 0;
    for (const id of blockIds) {
      const b = readBlock(id);
      if (b.n > 0 && b.weight > 0) s += b.weight;
    }
    const mode = $("bnMode").value;
    const suffix = mode === "prozent" ? " %" : "";
    $("bn_weight_sum").textContent = "Gesamtgewicht (nur Blöcke mit Noten): " +
      s.toFixed(2).replace(".", ",") + suffix;
    return s;
  }

  function showError(msg) { $("bn_err").textContent = msg || ""; }

  function calc() {
    showError("");
    const mode = $("bnMode").value;

    const blocks = blockIds.map(readBlock);
    const active = blocks.filter(b => b.n > 0 && b.weight > 0);
    const totalW = active.reduce((s, b) => s + b.weight, 0);

    const clearOutputs = () => {
      $("bn_grade_round").textContent = "–";
      $("bn_grade_exact").textContent = "–";
      $("bn_meta").textContent = "Noch keine Berechnung.";
      lastResult = null;
      $("bn_csv").disabled = true; $("bn_csv").setAttribute("aria-disabled", "true");
      $("bn_pdf").disabled = true; $("bn_pdf").setAttribute("aria-disabled", "true");
    };

    if (active.length === 0) {
      showError("Bitte mindestens einen Block mit Note und positivem Gewicht ausfüllen.");
      clearOutputs();
      return;
    }

    // Toleranz 0.05 % lässt 33,33 + 33,33 + 33,33 = 99,99 % zu.
    if (mode === "prozent" && Math.abs(totalW - 100) > 0.05) {
      showError("Prozentgewichtung ungültig: Summe = " + totalW.toFixed(2).replace(".", ",") +
                " %. Erwartet: 100 %.");
      clearOutputs();
      return;
    }
    if (mode === "anteilig" && totalW <= 0) {
      showError("Gesamtgewichtung muss > 0 sein.");
      clearOutputs();
      return;
    }

    const denom = mode === "prozent" ? 100 : totalW;
    let total = 0;
    for (const b of blocks) {
      b.gewichtet = (b.n > 0 && b.weight > 0 && denom > 0) ? (b.ungewichtet * (b.weight / denom)) : 0;
      total += b.gewichtet;
      const out = $("bn_result_" + b.id);
      if (out) {
        out.textContent = "Ungewichtet: " + b.ungewichtet.toFixed(2).replace(".", ",") +
                          " · Gewichtet: " + b.gewichtet.toFixed(2).replace(".", ",");
      }
    }

    $("bn_grade_round").textContent = (Math.round(total * 10) / 10).toFixed(1).replace(".", ",");
    $("bn_grade_exact").textContent = total.toFixed(2).replace(".", ",");
    $("bn_meta").textContent = "Gewichtung: " + (mode === "prozent" ? "prozentual" : "anteilig") +
                               " · Gesamt-Gewicht: " + totalW.toFixed(2).replace(".", ",") +
                               (mode === "prozent" ? " %" : "");

    lastResult = { mode, totalW, total, rounded: Math.round(total * 10) / 10, blocks };
    $("bn_csv").disabled = false; $("bn_csv").setAttribute("aria-disabled", "false");
    $("bn_pdf").disabled = false; $("bn_pdf").setAttribute("aria-disabled", "false");
    updateWeightSum();
  }

  // ── CSV ─────────────────────────────────────────────────────
  function exportCSV() {
    if (!lastResult) return;
    const r = lastResult;
    const rows = [["Blocknoten — Auswertung"], []];
    rows.push(["Gewichtungstyp", r.mode === "prozent" ? "prozentual" : "anteilig"]);
    rows.push(["Gesamt-Gewicht", String(r.totalW).replace(".", ",")]);
    rows.push([]);
    rows.push(["Block", "Name", "Gewicht", "Anzahl Noten", "Ungewichtet", "Gewichtet"]);
    for (const b of r.blocks) {
      rows.push([
        "Block " + b.id,
        b.name,
        String(b.weight).replace(".", ","),
        String(b.n),
        b.ungewichtet.toFixed(2).replace(".", ","),
        (b.gewichtet || 0).toFixed(2).replace(".", ",")
      ]);
    }
    rows.push([], ["Gesamtnote (genau)", r.total.toFixed(2).replace(".", ",")]);
    rows.push(["Gesamtnote (gerundet)", r.rounded.toFixed(1).replace(".", ",")]);
    E.exportCSV(rows, "blocknoten");
  }

  // ── PDF ─────────────────────────────────────────────────────
  function exportPDF() {
    if (!lastResult) return;
    const r = lastResult;
    E.exportPDF("blocknoten", (w) => {
      w.line("Blocknoten — Auswertung", 16, true);
      w.line("Gewichtung: " + (r.mode === "prozent" ? "prozentual" : "anteilig"), 11);
      w.line("Gesamt-Gewicht: " + String(r.totalW).replace(".", ",") + (r.mode === "prozent" ? " %" : ""), 11);
      w.blank(8);
      w.line("Blöcke", 12, true);
      const tableRows = r.blocks.map(b => [
        b.name,
        String(b.weight).replace(".", ","),
        String(b.n),
        b.ungewichtet.toFixed(2).replace(".", ","),
        (b.gewichtet || 0).toFixed(2).replace(".", ",")
      ]);
      w.table(["Name", "Gewicht", "Anz.", "Ungew.", "Gew."], tableRows, [0.36, 0.16, 0.12, 0.18, 0.18]);
      w.blank(10);
      w.line("Gesamtnote (genau): " + r.total.toFixed(2).replace(".", ","), 14, true);
      w.line("Gesamtnote (gerundet): " + r.rounded.toFixed(1).replace(".", ","), 14, true);
    });
  }

  // ── Reset ───────────────────────────────────────────────────
  function reset() {
    blockIds = [1]; nextId = 2;
    $("bnMode").value = "anteilig";
    renderBlocks();
    showError("");
    $("bn_grade_round").textContent = "–";
    $("bn_grade_exact").textContent = "–";
    $("bn_meta").textContent = "Noch keine Berechnung.";
    $("bn_csv").disabled = true; $("bn_csv").setAttribute("aria-disabled", "true");
    $("bn_pdf").disabled = true; $("bn_pdf").setAttribute("aria-disabled", "true");
    lastResult = null;
    urlUpdate();
  }

  // ── URL-State / Snapshots dynamisch ─────────────────────────
  function currentSchema() { return buildSchema(blockIds); }

  function init() {
    renderBlocks();
    defaults = S.captureDefaults(currentSchema());
    urlUpdate = (function () {
      let t = 0;
      return function () {
        if (t) return;
        t = setTimeout(() => {
          t = 0;
          const schema = currentSchema();
          // blockIds in URL einfügen
          const params = new URLSearchParams();
          params.set("bn_blocks", blockIds.join(","));
          for (const f of schema.fields) {
            const v = S.readField(f);
            if (v !== null && v !== "") params.set(f, v);
          }
          const target = "?" + params.toString();
          try { history.replaceState(null, "", target); } catch (e) {}
        }, 400);
      };
    })();

    // URL beim Laden anwenden
    const params = S.applyFromQueryString();
    if (params.has("bn_blocks")) {
      const list = params.get("bn_blocks").split(",")
        .map(x => parseInt(x, 10))
        .filter(n => Number.isFinite(n) && n > 0 && n < 100);
      if (list.length) {
        blockIds = list;
        nextId = Math.max(...list) + 1;
        renderBlocks();
      }
    }
    if ([...params.keys()].length) {
      const schema = currentSchema();
      S.applyFromParams(schema, params);
    }

    const form = $("bn_form");
    form.addEventListener("input", () => { updateWeightSum(); urlUpdate(); });
    form.addEventListener("change", () => { updateWeightSum(); urlUpdate(); });
    form.addEventListener("submit", (e) => { e.preventDefault(); calc(); });
    form.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && e.target.tagName !== "TEXTAREA") {
        e.preventDefault(); calc();
      }
    });

    $("bn_calc").addEventListener("click", calc);
    $("bn_reset").addEventListener("click", reset);
    $("bn_add_block").addEventListener("click", addBlock);
    $("bn_csv").addEventListener("click", exportCSV);
    $("bn_pdf").addEventListener("click", exportPDF);

    // Snapshots
    const snaps = S.snapshots(TOOL);
    const listEl = $("bn_snap_list");
    const flashEl = $("bn_flash");
    const flash = (m) => {
      flashEl.textContent = m; flashEl.style.opacity = "1";
      clearTimeout(flashEl._t);
      flashEl._t = setTimeout(() => { flashEl.style.opacity = "0"; }, 1800);
    };
    function rerender() {
      S.renderSnapshotList(listEl, snaps, currentSchema(), () => rerender());
    }
    rerender();

    $("bn_permalink").addEventListener("click", () => {
      // Permalink: auch blockIds mitsenden
      const schema = currentSchema();
      const params = new URLSearchParams();
      params.set("bn_blocks", blockIds.join(","));
      for (const f of schema.fields) {
        const v = S.readField(f);
        if (v !== null && v !== "") params.set(f, v);
      }
      const url = location.origin + location.pathname + "?" + params.toString();
      if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => flash("Permalink kopiert"))
          .catch(() => prompt("Permalink:", url));
      } else prompt("Permalink:", url);
    });
    $("bn_snap_add").addEventListener("click", () => {
      const name = prompt("Name für die Konfiguration:", "Blocknoten " + new Date().toLocaleDateString("de-DE"));
      if (name === null) return;
      const schema = currentSchema();
      // Snapshot inkl. blockIds speichern
      const params = new URLSearchParams();
      params.set("bn_blocks", blockIds.join(","));
      for (const f of schema.fields) {
        const v = S.readField(f);
        if (v !== null && v !== "") params.set(f, v);
      }
      // direkt in Snapshot-Storage schreiben
      const all = snaps.list();
      all.push({ name: String(name).slice(0, 80), data: params.toString(), ts: Date.now() });
      try { localStorage.setItem("nb_snap_" + TOOL, JSON.stringify(all)); } catch (e) {}
      rerender();
      flash("Schnappschuss gespeichert");
    });

    updateWeightSum();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
