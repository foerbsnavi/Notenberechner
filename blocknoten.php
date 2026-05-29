<?php
$pageTitle = 'Blocknoten & Zeugnisnote berechnen';
$breadcrumbName = 'Blocknoten';
$pageDescription = 'Zeugnis- und Halbjahresnote aus mehreren Blöcken berechnen: Klassenarbeiten, Tests, Mitarbeit, Referate. Prozentual oder anteilig, mit CSV- und PDF-Export.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Blocknoten & Zeugnisnote berechnen — Notenberechner';
$jsFiles = ['blocknoten.js'];
$needsPdf = true;
$extraSchemas = [
  ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [
    ['@type' => 'Question', 'name' => 'Was ist der Unterschied zwischen prozentualer und anteiliger Gewichtung?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Bei prozentualer Gewichtung muss die Summe aller Blöcke 100 % ergeben — sonst gibt es eine Warnung. Bei anteiliger Gewichtung sind beliebige positive Werte erlaubt, die intern normalisiert werden. Mathematisch identisch, nur die Eingabe ist freier.']],
    ['@type' => 'Question', 'name' => 'Wie viele Blöcke und Einzelnoten kann ich anlegen?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Beliebig viele Blöcke (etwa Klassenarbeiten, Tests, Mitarbeit, Referate), jeweils mit bis zu fünf Einzelnoten. Blöcke lassen sich frei benennen und einzeln hinzufügen oder entfernen.']],
    ['@type' => 'Question', 'name' => 'Wie wird die Gesamtnote gerundet?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Das Ergebnis erscheint zweifach: einmal mit zwei Nachkommastellen für die interne Belegakte und einmal gerundet auf den gewünschten Notenschritt für das Zeugnis.']],
    ['@type' => 'Question', 'name' => 'Was passiert mit leeren Blöcken?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Blöcke ohne Einzelnoten oder ohne Gewicht werden bei der Berechnung übersprungen. Wenn dadurch keine verwertbaren Blöcke übrig bleiben, weist das Tool darauf hin.']],
    ['@type' => 'Question', 'name' => 'Kann ich den gespeicherten Notenschlüssel übernehmen?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Einzelnoten dürfen wahlweise direkt als Note oder als Punkte über den unter „Notenschlüssel" gespeicherten Schlüssel eingegeben werden.']],
  ]],
];
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

  <section class="info-block" aria-labelledby="bn_info_h2">
    <h2 id="bn_info_h2">Blocknoten — Zeugnis- und Halbjahresnote berechnen</h2>
    <div class="info-grid">
      <article aria-labelledby="bn_info_a_h">
        <h3 id="bn_info_a_h">Zeugnisnote sauber zusammenführen</h3>
        <p>
          In den meisten Bundesländern fließen mehrere Bestandteile in die
          Zeugnis- oder Halbjahresnote ein: <strong>Klassenarbeiten</strong>,
          kürzere Tests, mündliche <strong>Mitarbeit</strong>, Referate,
          Projekte oder fachpraktische Leistungen. Blocknoten bildet diese
          Struktur direkt nach.
        </p>
        <p>
          Jeder Block bekommt einen Namen, ein Gewicht und bis zu fünf
          Einzelnoten — wahlweise direkt als Note oder als Punkte über den
          gespeicherten <a href="notenschluessel">Notenschlüssel</a>. Das
          Werkzeug liefert pro Block den <strong>ungewichteten</strong>
          Mittelwert und den <strong>gewichteten</strong> Anteil — und am
          Ende die Gesamtnote in zwei Varianten: zwei Nachkommastellen für
          die Belegakte und gerundet auf den gewünschten Notenschritt für
          das Zeugnis.
        </p>
      </article>
      <article aria-labelledby="bn_info_b_h">
        <h3 id="bn_info_b_h">Prozentual oder anteilig gewichten</h3>
        <p>
          Bei der <strong>prozentualen Gewichtung</strong> muss die Summe
          aller Blöcke 100 % ergeben — wer abweicht, sieht eine deutliche
          Warnung. Praktisch, wenn die Schule oder Fachschaft feste
          Vorgaben gemacht hat („50 % schriftlich, 50 % mündlich").
        </p>
        <p>
          Die <strong>anteilige Gewichtung</strong> erlaubt beliebige
          positive Werte und normalisiert intern. Das ist flexibler, wenn
          Gewichte als Verhältnisse gedacht sind („Klassenarbeit zählt
          doppelt") — die Mathematik bleibt identisch, nur die Eingabe ist
          entspannter.
        </p>
      </article>
    </div>
  </section>

  <section class="faq-block" aria-labelledby="bn_faq_h2">
    <h2 id="bn_faq_h2">Häufige Fragen zu Blocknoten und Zeugnisnote</h2>

    <details class="faq-item">
      <summary>Was ist der Unterschied zwischen prozentualer und anteiliger Gewichtung?</summary>
      <div class="faq-answer">
        <p>Bei <strong>prozentualer Gewichtung</strong> muss die Summe aller
        Blöcke 100 % ergeben — sonst gibt es eine Warnung. Bei
        <strong>anteiliger Gewichtung</strong> sind beliebige positive Werte
        erlaubt, die intern normalisiert werden. Mathematisch identisch,
        nur die Eingabe ist freier.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Wie viele Blöcke und Einzelnoten kann ich anlegen?</summary>
      <div class="faq-answer">
        <p>Beliebig viele Blöcke (etwa Klassenarbeiten, Tests, Mitarbeit,
        Referate), jeweils mit bis zu fünf Einzelnoten. Blöcke lassen sich
        frei benennen und einzeln hinzufügen oder entfernen.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Wie wird die Gesamtnote gerundet?</summary>
      <div class="faq-answer">
        <p>Das Ergebnis erscheint zweifach: einmal mit zwei
        Nachkommastellen für die interne Belegakte und einmal gerundet auf
        den gewünschten Notenschritt für das Zeugnis.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Was passiert mit leeren Blöcken?</summary>
      <div class="faq-answer">
        <p>Blöcke ohne Einzelnoten oder ohne Gewicht werden bei der
        Berechnung übersprungen. Wenn dadurch keine verwertbaren Blöcke
        übrig bleiben, weist das Tool darauf hin.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich den gespeicherten Notenschlüssel übernehmen?</summary>
      <div class="faq-answer">
        <p>Ja. Einzelnoten dürfen wahlweise direkt als Note oder als Punkte
        über den unter <a href="notenschluessel">Notenschlüssel</a>
        gespeicherten Schlüssel eingegeben werden.</p>
      </div>
    </details>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
