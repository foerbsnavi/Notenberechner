<?php
$pageTitle = 'Note in Punkte umrechnen';
$breadcrumbName = 'Note → Punkte';
$pageDescription = 'Rückwärtsrechner: Wie viele Punkte sind für eine Zielnote nötig? Notenschlüssel übernehmen oder eigene Werte angeben — live berechnet, mit PDF-Export.';
$rootPath = '';
$schemaType = 'SoftwareApplication';
$schemaName = 'Note in Punkte umrechnen — Notenberechner';
$jsFiles = ['note-punkte.js'];
$needsPdf = true;
$extraSchemas = [
  ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [
    ['@type' => 'Question', 'name' => 'Wofür ist der Rückwärtsrechner gut?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Schülerinnen und Schüler können vorab sehen, wie viele Punkte sie für eine bestimmte Wunschnote brauchen. Lehrkräfte nutzen den Rechner zur Plausibilitätsprüfung beim Erstellen oder Anpassen eines Notenschlüssels.']],
    ['@type' => 'Question', 'name' => 'Was bedeutet die Meldung „Note nicht erreichbar"?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Die eingestellte Skala lässt die gewünschte Note rechnerisch nicht zu — etwa weil die Bestnote erst ab einer Punktzahl gilt, die über der Maximalpunktzahl liegt. In dem Fall sollte der Schlüssel angepasst werden.']],
    ['@type' => 'Question', 'name' => 'Kann ich ohne gespeicherten Notenschlüssel rechnen?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ja. Du gibst die Schlüsselparameter (Skala, Max-Punkte, Punktegrenzen) direkt im Tool an. Wenn du den Schlüssel zuvor in „Notenschlüssel" gespeichert hast, wird er automatisch übernommen.']],
    ['@type' => 'Question', 'name' => 'Wird die Mindestpunktzahl exakt oder gerundet ausgegeben?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Es wird die kleinste Punktzahl ausgegeben, die rechnerisch noch zur Zielnote führt — passend zum gewählten Notenschritt und (falls aktiviert) zu halben Punkten.']],
  ]],
];
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Note in Punkte umrechnen</h1>
  <p class="lead">Rückwärtsrechner: Was muss erreicht werden, um eine Zielnote zu bekommen?</p>

  <form id="np_form" class="card" aria-labelledby="np_h2" novalidate>
    <h2 id="np_h2">Eingaben</h2>
    <div class="grid">
      <div data-span="3" class="field">
        <label for="npGrade">Ziel-Note</label>
        <input id="npGrade" name="npGrade" type="number" step="0.01" min="0" value="2" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npHalfPts">Halbe Punkte?</label>
        <select id="npHalfPts" name="npHalfPts">
          <option value="yes" selected>ja</option>
          <option value="no">nein</option>
        </select>
      </div>
      <div data-span="3" class="field">
        <label for="npUseKey">Notenschlüssel</label>
        <select id="npUseKey" name="npUseKey">
          <option value="yes" selected>aus Notenschlüssel übernehmen</option>
          <option value="no">eigene Werte</option>
        </select>
        <p class="hint" id="np_key_hint" aria-live="polite"></p>
      </div>
      <div data-span="3" class="field">
        <label for="npGradeStep">Noten-Schritt</label>
        <select id="npGradeStep" name="npGradeStep">
          <option value="0.1">Zehntel</option>
          <option value="0.25">Viertel</option>
          <option value="0.3333333333333333">Drittel</option>
          <option value="0.5">Halbe</option>
          <option value="1" selected>Ganze</option>
        </select>
      </div>

      <div data-span="3" class="field">
        <label for="npBestG">Beste Note</label>
        <input id="npBestG" name="npBestG" type="number" step="0.01" value="1" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npWorstG">Schlechteste Note</label>
        <input id="npWorstG" name="npWorstG" type="number" step="0.01" value="6" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npMaxPts">Max. Punkte</label>
        <input id="npMaxPts" name="npMaxPts" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npBestFrom">Beste ab Punkte</label>
        <input id="npBestFrom" name="npBestFrom" type="number" step="0.5" min="0" value="60" inputmode="decimal">
      </div>
      <div data-span="3" class="field">
        <label for="npWorstFrom">Schlechteste ab Punkte</label>
        <input id="npWorstFrom" name="npWorstFrom" type="number" step="0.5" min="0" value="0" inputmode="decimal">
      </div>
    </div>

    <hr class="sep">
    <div class="actions">
      <button type="submit" class="btn primary" id="np_calc">Berechnen</button>
      <button type="button" class="btn ghost" id="np_reset" data-action="reset">Reset</button>
    </div>

    <div id="np_err" class="err" role="alert" aria-live="assertive"></div>
  </form>

  <section class="card" aria-labelledby="np_out_h2" aria-live="polite">
    <h2 id="np_out_h2">Ergebnis</h2>
    <p class="result-meta" id="np_meta">Noch keine Berechnung.</p>
    <output for="npGrade npMaxPts" id="np_pts" class="result-big">–</output>
    <p class="hint mt-sm" id="np_hint"></p>
  </section>

  <section class="card" aria-labelledby="np_post_h2">
    <h2 id="np_post_h2">Weiter verarbeiten</h2>
    <div class="actions">
      <button type="button" class="btn" id="np_pdf" disabled aria-disabled="true">PDF herunterladen</button>
      <button type="button" class="btn" id="np_permalink">Permalink in Zwischenablage</button>
      <button type="button" class="btn" id="np_snap_add">Aktuelle Konfiguration im Browser speichern</button>
    </div>
    <p class="hint flash-hint" id="np_flash"></p>
    <ol class="snap-list" id="np_snap_list" aria-live="polite"></ol>
  </section>

  <section class="info-block" aria-labelledby="np_info_h2">
    <h2 id="np_info_h2">Note in Punkte umrechnen</h2>
    <div class="info-grid">
      <article aria-labelledby="np_info_a_h">
        <h3 id="np_info_a_h">Warum rückwärts rechnen?</h3>
        <p>
          Wer eine <strong>Zielnote</strong> im Kopf hat, will wissen,
          wie viele Punkte dafür reichen. Genau das macht dieser Rechner:
          Zielnote eingeben, Punktegrenze ablesen — sauber gerundet im
          eingestellten Notenschritt.
        </p>
        <p>
          Das ist nicht nur für Schülerinnen und Schüler praktisch, die
          eine bestimmte Note erreichen wollen. Auch Lehrkräfte nutzen den
          Rückwärtsrechner, um <strong>Bewertungsgrenzen zu plausibilisieren</strong>
          — etwa: „Wie viele Punkte braucht es noch für eine Drei?"
        </p>
      </article>
      <article aria-labelledby="np_info_b_h">
        <h3 id="np_info_b_h">Transparenz vor der Klausur</h3>
        <p>
          Wenn der <a href="notenschluessel">Notenschlüssel</a> bereits steht,
          lässt sich vor der Klausur konkret kommunizieren, welche Punktzahl
          welche Note ergibt. Das schafft <strong>Klarheit</strong> bei
          allen Beteiligten und reduziert Diskussionen nach der Rückgabe.
        </p>
        <p>
          Ist eine Note mit der gewählten Skala nicht erreichbar — etwa eine
          1,0 bei zu hoch gesetzter „Bestnote ab"-Schwelle — meldet der
          Rechner das deutlich. So fällt früh auf, wenn der Schlüssel
          ungewollt zu streng eingestellt ist.
        </p>
      </article>
    </div>
  </section>

  <section class="faq-block" aria-labelledby="np_faq_h2">
    <h2 id="np_faq_h2">Häufige Fragen zu „Note in Punkte umrechnen"</h2>

    <details class="faq-item">
      <summary>Wofür ist der Rückwärtsrechner gut?</summary>
      <div class="faq-answer">
        <p>Schülerinnen und Schüler können vorab sehen, wie viele Punkte
        sie für eine bestimmte Wunschnote brauchen. Lehrkräfte nutzen den
        Rechner zur <strong>Plausibilitätsprüfung</strong> beim Erstellen
        oder Anpassen eines Notenschlüssels.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Was bedeutet die Meldung „Note nicht erreichbar"?</summary>
      <div class="faq-answer">
        <p>Die eingestellte Skala lässt die gewünschte Note rechnerisch
        nicht zu — etwa weil die Bestnote erst ab einer Punktzahl gilt, die
        über der Maximalpunktzahl liegt. In dem Fall sollte der
        <a href="notenschluessel">Notenschlüssel</a> angepasst werden.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Kann ich ohne gespeicherten Notenschlüssel rechnen?</summary>
      <div class="faq-answer">
        <p>Ja. Du gibst die Schlüsselparameter (Skala, Max-Punkte,
        Punktegrenzen) direkt im Tool an. Wenn du den Schlüssel zuvor in
        <a href="notenschluessel">Notenschlüssel</a> gespeichert hast, wird
        er automatisch übernommen.</p>
      </div>
    </details>

    <details class="faq-item">
      <summary>Wird die Mindestpunktzahl exakt oder gerundet ausgegeben?</summary>
      <div class="faq-answer">
        <p>Es wird die kleinste Punktzahl ausgegeben, die rechnerisch noch
        zur Zielnote führt — passend zum gewählten Notenschritt und (falls
        aktiviert) zu halben Punkten.</p>
      </div>
    </details>
  </section>
</main>
<?php include '_inc/footer.php'; ?>
