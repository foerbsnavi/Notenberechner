<?php
$pageTitle = 'Notenschlüssel';
$pageDescription = 'Linearen Notenschlüssel erstellen: Punkte zu Noten in beliebiger Skala. Mit halben Punkten, frei wählbarem Notenschritt, CSV- und PDF-Export.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Notenschlüssel — Notenberechner';
$jsFiles = ['notenschluessel.js'];
$needsPdf = true;
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Notenschlüssel</h1>
  <p class="lead">Linearer Punkte-zu-Noten-Schlüssel mit beliebiger Skala. Wird automatisch von „Punkte → Note", „Note → Punkte" und „Klausur-Statistik" übernommen.</p>

  <form id="ns_form" class="card" aria-labelledby="ns_h2" novalidate>
    <h2 id="ns_h2">Eingaben</h2>

    <div class="grid">
      <div data-span="3" class="field">
        <label for="bestGrade">Beste Note</label>
        <input id="bestGrade" name="bestGrade" type="number" step="0.01" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="worstGrade">Schlechteste Note</label>
        <input id="worstGrade" name="worstGrade" type="number" step="0.01" value="6" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="maxPts">Maximale Punktzahl</label>
        <input id="maxPts" name="maxPts" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="halfPts">Halbe Punkte zählen?</label>
        <select id="halfPts" name="halfPts">
          <option value="yes" selected>ja</option>
          <option value="no">nein</option>
        </select>
      </div>

      <div data-span="6" class="field">
        <label for="bestFromPts">Beste Note vergeben ab Punkten</label>
        <input id="bestFromPts" name="bestFromPts" type="number" step="0.5" value="60" inputmode="decimal">
        <p class="hint">Standard: maximale Punktzahl.</p>
      </div>
      <div data-span="6" class="field">
        <label for="worstFromPts">Schlechteste Note vergeben ab Punkten</label>
        <input id="worstFromPts" name="worstFromPts" type="number" step="0.5" value="0" inputmode="decimal">
        <p class="hint">Standard: 0.</p>
      </div>

      <div data-span="6" class="field">
        <label for="gradeStep">Noten vergeben als</label>
        <select id="gradeStep" name="gradeStep">
          <option value="0.1">Zehntelnoten (1,1 / 1,2 …)</option>
          <option value="0.25">Viertelnoten (1,25 / 1,5 …)</option>
          <option value="0.3333333333333333">Drittelnoten (1,000 / 1,333 / 1,667 …)</option>
          <option value="0.5">halbe Noten (1,0 / 1,5 …)</option>
          <option value="1" selected>ganze Noten (1, 2, 3 …)</option>
        </select>
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="ns_calc">Berechnen</button>
      <button type="button" class="btn ghost" id="ns_reset" data-action="reset">Reset</button>
    </div>

    <div id="ns_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="ns_out_h2" id="ns_out_section" aria-live="polite">
    <h2 id="ns_out_h2">Ergebnis</h2>
    <p class="result-meta" id="ns_meta">Noch keine Berechnung.</p>
    <div class="table-wrap">
      <table class="table table--compact" id="ns_table" hidden>
        <thead>
          <tr>
            <th scope="col">Punkte</th>
            <th scope="col">Note</th>
            <th class="col-filler" aria-hidden="true"></th>
          </tr>
        </thead>
        <tbody id="ns_tbody"></tbody>
      </table>
    </div>
    <p class="hint mt-sm" id="ns_default_hint"></p>
  </section>

  <section class="card" aria-labelledby="ns_post_h2">
    <h2 id="ns_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="ns_csv" disabled aria-disabled="true">CSV herunterladen</button>
      <button type="button" class="btn" id="ns_pdf" disabled aria-disabled="true">PDF herunterladen</button>
      <button type="button" class="btn" id="ns_permalink">Permalink in Zwischenablage</button>
      <button type="button" class="btn" id="ns_snap_add">Aktuelle Konfiguration im Browser speichern</button>
    </div>
    <p class="hint flash-hint" id="ns_flash"></p>
    <ol class="snap-list" id="ns_snap_list" aria-live="polite"></ol>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
