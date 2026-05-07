<?php
$pageTitle = null; // Hub: Brand wird der Title
$pageDescription = 'Notenberechner für Lehrkräfte: Notenschlüssel erstellen, Punkte in Noten umrechnen, Klausur-Statistiken, Blocknoten, Beurteilungen schreiben — alles im Browser, ohne Anmeldung.';
$rootPath = '';
$schemaType = 'WebSite';
$jsFiles = ['hub.js'];
include '_inc/head.php';
?>
<main id="main" class="layout">
  <h1>Notenberechner</h1>
  <p class="lead">
    Sieben Werkzeuge für Lehrkräfte. Alles läuft direkt im Browser, nichts wird
    gespeichert oder versendet. Konfigurationen lassen sich per Permalink teilen
    und als Schnappschuss merken.
  </p>

  <div class="hub-grid">
    <a class="hub-card" href="notenschluessel">
      <h2>1 · Notenschlüssel</h2>
      <p>Linearen Punkte-zu-Noten-Schlüssel generieren — frei skalierbar, mit CSV- und PDF-Export.</p>
    </a>
    <a class="hub-card" href="punkte-note">
      <h2>2 · Punkte → Note</h2>
      <p>Aus erreichten Punkten direkt die Note ablesen, optional mit dem gespeicherten Schlüssel.</p>
    </a>
    <a class="hub-card" href="note-punkte">
      <h2>3 · Note → Punkte</h2>
      <p>Rückwärts: Wie viele Punkte sind für eine Zielnote nötig?</p>
    </a>
    <a class="hub-card" href="statistik">
      <h2>4 · Klausur-Statistik</h2>
      <p>Werte einfügen, Kennzahlen und Verteilung erhalten — mit CSV- und PDF-Export.</p>
    </a>
    <a class="hub-card" href="blocknoten">
      <h2>5 · Blocknoten</h2>
      <p>Mehrere Bewertungsblöcke gewichtet zusammenrechnen, prozentual oder anteilig.</p>
    </a>
    <a class="hub-card" href="beurteilung">
      <h2>6 · Beurteilung</h2>
      <p>Textbausteine für Schüler-Beurteilungen — Note, Fach, Länge wählen, fertig.</p>
    </a>
    <a class="hub-card" href="wuerfeln">
      <h2>7 · Note würfeln</h2>
      <p>Zufällige Note ziehen, optional mit Tendenz nach oben oder unten.</p>
    </a>
  </div>

  <section class="card mt-lg" aria-labelledby="hub_snap_h2">
    <h2 id="hub_snap_h2">Gespeicherte Berechnungen</h2>
    <p class="hint">Konfigurationen, die du in den einzelnen Werkzeugen über
       „Aktuelle Konfiguration im Browser speichern" abgelegt hast. Klick auf
       „laden" öffnet das passende Werkzeug mit den hinterlegten Werten.</p>
    <ol class="snap-list" id="hub_snap_list" aria-live="polite" hidden></ol>
    <p class="hint" id="hub_snap_empty" hidden>Noch keine Konfigurationen gespeichert.</p>
  </section>

  <div class="hub-disclaimer">
    Alle Berechnungen erfolgen ohne Gewähr und ohne Datenspeicherung auf einem Server.
    Konfigurationen liegen ausschließlich im lokalen Speicher deines Browsers.
  </div>
</main>
<?php include '_inc/footer.php'; ?>
