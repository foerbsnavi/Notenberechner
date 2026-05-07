<?php
$pageTitle = 'Datenschutz';
$pageDescription = 'Datenschutzerklärung des Notenberechners.';
$rootPath = '';
$noindex = true;
include '_inc/head.php';
?>
<main id="main" class="page">
  <article class="prose">
    <div id="datenschutz-container">
      <h1>Datenschutz</h1>
      <p>
        Diese Seite verarbeitet keine personenbezogenen Daten serverseitig.
        Alle Berechnungen laufen ausschließlich im Browser. Konfigurationen
        und Schnappschüsse werden nur im lokalen Speicher (localStorage)
        deines Geräts abgelegt und nicht an einen Server übertragen.
      </p>
      <p>
        Die ausführliche Datenschutzerklärung wird normalerweise extern geladen.
        Falls dieser Text dauerhaft sichtbar bleibt, ist das externe Skript
        gerade nicht erreichbar — die obigen Aussagen zur lokalen
        Datenverarbeitung gelten dennoch.
      </p>
      <p>
        Verantwortlich: Fabian Brose, Johann-Sebastian-Bach-Str. 16, 74219 Möckmühl.
        Kontakt: <a href="mailto:z@xdr5.de">z(at)xdr5.de</a>
      </p>
    </div>
    <script src="https://daten.fabianbrose.de/datenschutz/datenschutz.js"></script>
  </article>
</main>
<?php include '_inc/footer.php'; ?>
