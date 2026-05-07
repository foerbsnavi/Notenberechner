<?php
$pageTitle = 'Blocknoten';
$pageDescription = 'Blocknoten gewichtet zusammenrechnen — Klassenarbeiten, Tests, Mitarbeit, Referate. Prozentual oder anteilig, mit CSV- und PDF-Export.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Blocknoten — Notenberechner';
$jsFiles = ['blocknoten.js'];
$needsPdf = true;
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Blocknoten</h1>
  <p class="lead">Mehrere Bewertungsblöcke gewichtet zusammenfassen — z.B. Klassenarbeiten, Tests, Mitarbeit, Referate.</p>

  <form id="bn_form" class="card" aria-labelledby="bn_h2" novalidate>
    <h2 id="bn_h2">Eingaben</h2>

    <div class="grid">
      <div data-span="6" class="field">
        <label for="bnMode">Gewichtungstyp</label>
        <select id="bnMode" name="bnMode">
          <option value="anteilig" selected>Anteilig (beliebige Werte, normalisiert)</option>
          <option value="prozent">Prozentual (Summe = 100 %)</option>
        </select>
      </div>
      <div data-span="6" class="field actions-end">
        <div class="actions">
          <button type="button" class="btn" id="bn_add_block">+ Block hinzufügen</button>
        </div>
      </div>
    </div>

    <div id="bn_blocks" class="blocks mt-lg"></div>

    <p class="hint" id="bn_weight_sum" aria-live="polite">Gesamtgewicht: 0</p>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="bn_calc">Berechnen</button>
      <button type="button" class="btn ghost" id="bn_reset" data-action="reset">Reset</button>
    </div>

    <div id="bn_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="bn_out_h2" aria-live="polite">
    <h2 id="bn_out_h2">Ergebnis</h2>
    <p class="result-meta" id="bn_meta">Noch keine Berechnung.</p>
    <div class="result-grid">
      <div>
        <span class="hint">Gerundet (eine Stelle):</span><br>
        <output for="bn_blocks" id="bn_grade_round" class="result-big">–</output>
      </div>
      <div>
        <span class="hint">Genau (zwei Stellen):</span>
        <output for="bn_blocks" id="bn_grade_exact" class="tabnum">–</output>
      </div>
    </div>
  </section>

  <section class="card" aria-labelledby="bn_post_h2">
    <h2 id="bn_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="bn_csv" disabled aria-disabled="true">CSV herunterladen</button>
      <button type="button" class="btn" id="bn_pdf" disabled aria-disabled="true">PDF herunterladen</button>
      <button type="button" class="btn" id="bn_permalink">Permalink in Zwischenablage</button>
      <button type="button" class="btn" id="bn_snap_add">Aktuelle Konfiguration im Browser speichern</button>
    </div>
    <p class="hint flash-hint" id="bn_flash"></p>
    <ol class="snap-list" id="bn_snap_list" aria-live="polite"></ol>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
