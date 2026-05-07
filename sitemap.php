<?php
header('Content-Type: application/xml; charset=UTF-8');

$base  = 'https://noten.brosemedien.de';
$today = date('Y-m-d');

// Pfade relativ zu diesem File für filemtime()
$root = __DIR__ . '/';

$entries = [
    ['loc' => '/',                  'file' => 'index.php',           'priority' => '1.0', 'change' => 'monthly'],
    ['loc' => '/notenschluessel',   'file' => 'notenschluessel.php', 'priority' => '0.8', 'change' => 'monthly'],
    ['loc' => '/punkte-note',       'file' => 'punkte-note.php',     'priority' => '0.8', 'change' => 'monthly'],
    ['loc' => '/note-punkte',       'file' => 'note-punkte.php',     'priority' => '0.8', 'change' => 'monthly'],
    ['loc' => '/statistik',         'file' => 'statistik.php',       'priority' => '0.8', 'change' => 'monthly'],
    ['loc' => '/blocknoten',        'file' => 'blocknoten.php',      'priority' => '0.8', 'change' => 'monthly'],
    ['loc' => '/beurteilung',       'file' => 'beurteilung.php',     'priority' => '0.8', 'change' => 'monthly'],
    ['loc' => '/wuerfeln',          'file' => 'wuerfeln.php',        'priority' => '0.8', 'change' => 'monthly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($entries as $e) {
    $mt = @filemtime($root . $e['file']);
    $lastmod  = $mt ? date('Y-m-d', $mt) : $today;
    $loc      = htmlspecialchars($base . $e['loc'], ENT_QUOTES, 'UTF-8');
    $change   = htmlspecialchars($e['change'],     ENT_QUOTES, 'UTF-8');
    $priority = htmlspecialchars($e['priority'],   ENT_QUOTES, 'UTF-8');
    echo "  <url>\n";
    echo "    <loc>$loc</loc>\n";
    echo "    <lastmod>$lastmod</lastmod>\n";
    echo "    <changefreq>$change</changefreq>\n";
    echo "    <priority>$priority</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
