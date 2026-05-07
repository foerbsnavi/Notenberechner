<?php
$pageTitle = 'Beurteilung';
$pageDescription = 'Beurteilungstexte für Schüler generieren — Schülername, Fach, Note und Textlänge wählen, Text bekommen. Editierbar, mit Kopier- und PDF-Funktion.';
$rootPath = '';
$schemaType = 'WebApplication';
$schemaName = 'Beurteilungsgenerator — Notenberechner';
$jsFiles = ['beurteilung.js'];
$needsPdf = true;
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Beurteilungsgenerator</h1>
  <p class="lead">Textbausteine für Schüler-Beurteilungen — generieren, anpassen, kopieren oder als PDF exportieren.</p>

  <form id="bg_form" class="card" aria-labelledby="bg_h2" novalidate>
    <h2 id="bg_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="3" class="field">
        <label for="bgName">Schülername</label>
        <input id="bgName" name="bgName" type="text" maxlength="40" placeholder="z.B. Alex">
      </div>
      <div data-span="3" class="field">
        <label for="bgSubject">Unterrichtsbereich</label>
        <select id="bgSubject" name="bgSubject">
          <option value="Mathe" selected>Mathe</option>
          <option value="Deutsch">Deutsch</option>
          <option value="Englisch">Englisch</option>
          <option value="Kunst">Kunst</option>
          <option value="Musik">Musik</option>
          <option value="Sport">Sport</option>
          <option value="Biologie">Biologie</option>
          <option value="Geschichte">Geschichte</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="bgGrade">Note</label>
        <select id="bgGrade" name="bgGrade">
          <option value="1">1</option>
          <option value="2" selected>2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
          <option value="6">6</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="bgLen">Textlänge</label>
        <select id="bgLen" name="bgLen">
          <option value="short">kurz</option>
          <option value="medium" selected>mittel</option>
          <option value="long">lang</option>
        </select>
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="bg_gen">Beurteilung generieren</button>
      <button type="button" class="btn ghost" id="bg_reset" data-action="reset">Reset</button>
    </div>

    <div id="bg_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="bg_out_h2" aria-live="polite">
    <h2 id="bg_out_h2">Ergebnis</h2>
    <p class="result-meta" id="bg_meta">Noch keine Beurteilung.</p>
    <label for="bg_text" class="sr-only">Generierte Beurteilung (editierbar)</label>
    <textarea id="bg_text" rows="6" placeholder="Der Text erscheint hier…"></textarea>
    <p class="hint">Du kannst den Text vor dem Kopieren oder Exportieren noch anpassen.</p>
  </section>

  <section class="card" aria-labelledby="bg_post_h2">
    <h2 id="bg_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="bg_copy" disabled aria-disabled="true">Kopieren</button>
      <button type="button" class="btn" id="bg_pdf" disabled aria-disabled="true">PDF herunterladen</button>
    </div>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
