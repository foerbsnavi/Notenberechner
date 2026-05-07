<?php
// Erwartet: $pageTitle (Tool-Name oder null für Hub),
//           optional $pageDescription, $rootPath, $noindex, $schemaType,
//           $schemaName, $jsFiles (Array)
$brand           = 'Notenberechner';
$siteHost        = 'noten.brosemedien.de';
$pageTitleRaw    = $pageTitle ?? null;
$pageTitle       = $pageTitleRaw
    ? $pageTitleRaw . ' — ' . $brand . ' | ' . $siteHost
    : $brand . ' | ' . $siteHost;
$pageDescription = $pageDescription ?? 'Notenberechner für Lehrkräfte: Notenschlüssel, Punkte zu Note, Klausur-Statistik, Blocknoten, Beurteilungen und mehr. Im Browser, ohne Anmeldung, ohne Datenspeicherung.';
$rootPath        = $rootPath ?? '';
$noindex         = $noindex   ?? false;
$schemaType      = $schemaType ?? 'WebApplication';
$schemaName      = $schemaName ?? ($pageTitleRaw ? ($pageTitleRaw . ' — ' . $brand) : $brand);

// Host fest auf den Soll-Wert — schützt gegen Host-Header-Injection in Canonical/og-Tags.
$reqHost   = $_SERVER['HTTP_HOST'] ?? '';
$host      = preg_match('/^[a-z0-9.\-]+\.brosemedien\.de$/i', $reqHost) ? $reqHost : $siteHost;
$scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$path      = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$canonical = $scheme . '://' . $host . '/' . ltrim((string)$path, '/');
$canonical = preg_replace('#(?<!:)/+#', '/', $canonical); // Doppel-Slashes vermeiden
$ogImage   = $scheme . '://' . $host . '/og-image.svg';

$cssMtime = @filemtime(__DIR__ . '/../styles/style.css') ?: time();
?><!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="robots" content="<?= $noindex ? 'noindex, nofollow' : 'index, follow' ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card" content="summary_large_image">

  <link rel="icon" href="<?= $rootPath ?>favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="<?= $rootPath ?>styles/style.css?v=<?= $cssMtime ?>">

<?php if (empty($noindex)): ?>
  <script type="application/ld+json">
  <?php
    $schema = [
      '@context'    => 'https://schema.org',
      '@type'       => $schemaType,
      'name'        => $schemaName,
      'description' => $pageDescription,
      'url'         => $canonical,
    ];
    if ($schemaType === 'SoftwareApplication') {
      $schema['applicationCategory'] = 'EducationalApplication';
      $schema['operatingSystem']     = 'Web';
      $schema['offers']              = ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'];
    } elseif ($schemaType === 'WebApplication') {
      $schema['applicationCategory'] = 'EducationalApplication';
      $schema['operatingSystem']     = 'Web';
      $schema['offers']              = ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'];
    }
    echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  ?>
  </script>
<?php endif; ?>
</head>
<body>
<a class="skip-link" href="#main">Zum Inhalt springen</a>
<header class="site-header">
  <div class="header-inner">
    <a href="<?= $rootPath ?>" class="brand" aria-label="Notenberechner — Startseite">
      <svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false">
        <rect x="2" y="2" width="28" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="1.5"/>
        <text x="16" y="22" text-anchor="middle" font-family="-apple-system,Segoe UI,Roboto,Arial,sans-serif" font-size="18" font-weight="700" fill="currentColor">N</text>
      </svg>
      <span class="brand-text">Notenberechner</span>
    </a>
    <p class="brand-tagline">Werkzeugkasten für Lehrkräfte — schnell, klar, ohne Anmeldung.</p>
  </div>
</header>
<?php
$currentSlug = trim($path, '/');
include __DIR__ . '/nav.php';
?>
