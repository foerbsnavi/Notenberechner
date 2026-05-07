<?php
$pageTitle = 'Note würfeln';
$pageDescription = 'Zufällige Note würfeln — mit Tendenz nach oben oder unten, in Zehntel-, Viertel-, Drittel-, Halbe- oder ganzen Noten und frei wählbarem Bereich. Direkt im Browser.';
$rootPath = '';
$schemaType = 'WebApplication';
$schemaName = 'Note würfeln — Notenberechner';
$jsFiles = ['wuerfeln.js'];
$needsPdf = true;
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Note würfeln</h1>
  <p class="lead">Zufällige Note ziehen — mit oder ohne Tendenz, in verschiedenen Notenschritten.</p>

  <form id="dc_form" class="card" aria-labelledby="dc_h2" novalidate>
    <h2 id="dc_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="6" class="field">
        <label for="dcFormat">Notenformat</label>
        <select id="dcFormat" name="dcFormat">
          <option value="1">Ganze Note (1, 2, 3 …)</option>
          <option value="0.5">Halbe Note (1,0 / 1,5 …)</option>
          <option value="0.25">Viertelnoten (1,25 / 1,5 …)</option>
          <option value="0.3333333333333333">Drittelnoten (1,000 / 1,333 …)</option>
          <option value="0.1" selected>Zehntelnoten (1,1 / 1,2 …)</option>
        </select>
      </div>
      <div data-span="6" class="field">
        <label for="dcBias">Tendenz</label>
        <select id="dcBias" name="dcBias">
          <option value="random" selected>Zufall</option>
          <option value="good">Eher gut</option>
          <option value="bad">Eher nicht so gut</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="dcMin">Min. Note</label>
        <input id="dcMin" name="dcMin" type="number" step="0.1" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="dcMax">Max. Note</label>
        <input id="dcMax" name="dcMax" type="number" step="0.1" value="6" inputmode="decimal">
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="dc_roll">
        <span aria-hidden="true">🎲</span> Würfeln
      </button>
      <button type="button" class="btn ghost" id="dc_reset" data-action="reset">Reset</button>
    </div>

    <div id="dc_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="dc_out_h2" aria-live="polite">
    <h2 id="dc_out_h2">Ergebnis</h2>
    <p class="result-meta" id="dc_meta">Noch nicht gewürfelt.</p>
    <output for="dcMin dcMax" id="dc_out" class="result-big">–</output>
  </section>

  <section class="card" aria-labelledby="dc_post_h2">
    <h2 id="dc_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="dc_pdf" disabled aria-disabled="true">PDF herunterladen</button>
    </div>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
