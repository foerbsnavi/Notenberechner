<?php
$pageTitle = 'Punkte zu Note';
$pageDescription = 'Aus erreichten Punkten direkt die Note ablesen. Optional mit gespeichertem Notenschlüssel oder eigenen Werten.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Punkte zu Note — Notenberechner';
$jsFiles = ['punkte-note.js'];
$needsPdf = true;
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Punkte → Note</h1>
  <p class="lead">Aus erreichten Punkten unmittelbar die zugehörige Note ermitteln. Eingaben werden live verarbeitet.</p>

  <form id="pn_form" class="card" aria-labelledby="pn_h2" novalidate>
    <h2 id="pn_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="3" class="field">
        <label for="pnPoints">Erreichte Punkte</label>
        <input id="pnPoints" name="pnPoints" type="number" step="0.5" min="0" value="0" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnHalfPts">Halbe Punkte?</label>
        <select id="pnHalfPts" name="pnHalfPts">
          <option value="yes" selected>ja</option>
          <option value="no">nein</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="pnUseKey">Notenschlüssel</label>
        <select id="pnUseKey" name="pnUseKey">
          <option value="yes" selected>aus Notenschlüssel übernehmen</option>
          <option value="no">eigene Werte</option>
        </select>
        <p class="hint" id="pn_key_hint" aria-live="polite"></p>
      </div>
      <div data-span="3" class="field">
        <label for="pnGradeStep">Noten-Schritt</label>
        <select id="pnGradeStep" name="pnGradeStep">
          <option value="0.1">Zehntel</option>
          <option value="0.25">Viertel</option>
          <option value="0.3333333333333333">Drittel</option>
          <option value="0.5">Halbe</option>
          <option value="1" selected>Ganze</option>
        </select>
      </div>

      <div data-span="3" class="field">
        <label for="pnBestG">Beste Note</label>
        <input id="pnBestG" name="pnBestG" type="number" step="0.01" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnWorstG">Schlechteste Note</label>
        <input id="pnWorstG" name="pnWorstG" type="number" step="0.01" value="6" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnMaxPts">Max. Punkte</label>
        <input id="pnMaxPts" name="pnMaxPts" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnBestFrom">Beste ab Punkte</label>
        <input id="pnBestFrom" name="pnBestFrom" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="pnWorstFrom">Schlechteste ab Punkte</label>
        <input id="pnWorstFrom" name="pnWorstFrom" type="number" step="0.5" min="0" value="0" inputmode="decimal">
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="pn_calc">Berechnen</button>
      <button type="button" class="btn ghost" id="pn_reset" data-action="reset">Reset</button>
    </div>

    <div id="pn_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="pn_out_h2" aria-live="polite">
    <h2 id="pn_out_h2">Ergebnis</h2>
    <p class="result-meta" id="pn_meta">Noch keine Berechnung.</p>
    <output for="pnPoints pnMaxPts" id="pn_grade" class="result-big">–</output>
  </section>

  <section class="card" aria-labelledby="pn_post_h2">
    <h2 id="pn_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="pn_pdf" disabled aria-disabled="true">PDF herunterladen</button>
      <button type="button" class="btn" id="pn_permalink">Permalink in Zwischenablage</button>
      <button type="button" class="btn" id="pn_snap_add">Aktuelle Konfiguration im Browser speichern</button>
    </div>
    <p class="hint flash-hint" id="pn_flash"></p>
    <ol class="snap-list" id="pn_snap_list" aria-live="polite"></ol>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
