<?php
/**
 * One-time Composer installer helper.
 *
 * 1. Upload this file to the server root (next to index.php).
 * 2. Visit:  https://yourdomain.com/run_composer_install.php?token=handycrm2026
 * 3. DELETE this file from the server when done.
 *
 * ⚠️  This file has a secret token to prevent unauthorised access.
 *     Do NOT leave it on the server after use.
 */

define('SECRET_TOKEN', 'handycrm2026');

// ── Auth ──────────────────────────────────────────────────────────────────────
if (($_GET['token'] ?? '') !== SECRET_TOKEN) {
    http_response_code(403);
    die('403 Forbidden — add ?token=handycrm2026 to the URL.');
}

$rootDir    = __DIR__;
$pharPath   = $rootDir . '/composer.phar';
$composerJson = $rootDir . '/composer.json';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<title>HandyCRM — Composer Install</title>
<style>
  body{font-family:monospace;background:#1e1e2e;color:#cdd6f4;padding:2rem;margin:0}
  h2{color:#89b4fa} .ok{color:#a6e3a1} .err{color:#f38ba8} .warn{color:#fab387}
  pre{background:#181825;padding:1rem;border-radius:6px;white-space:pre-wrap;word-break:break-all}
  .box{max-width:860px;margin:auto}
  .step{margin:1.2rem 0;padding:.8rem 1rem;background:#181825;border-radius:6px;border-left:4px solid #89b4fa}
</style>
</head>
<body><div class="box">
<h2>🔧 HandyCRM — Composer Dependency Installer</h2>
<p>Installing <strong>smalot/pdfparser</strong> and any other missing packages…</p>
<?php

$log = [];

function step(string $title, string $status = 'ok'): void {
    echo "<div class='step'><span class='{$status}'>[{$status}]</span> " . htmlspecialchars($title) . "</div>\n";
    flush();
}

function runCmd(string $cmd): array {
    $output = []; $code = 0;
    exec($cmd . ' 2>&1', $output, $code);
    return ['out' => implode("\n", $output), 'code' => $code];
}

// ── Check exec() is available ──────────────────────────────────────────────
$disabled = array_map('trim', explode(',', ini_get('disable_functions') ?: ''));
if (in_array('exec', $disabled)) {
    step('exec() is disabled on this server — cannot run composer automatically.', 'err');
    echo "<p class='err'>Contact your hosting provider to enable exec() or install composer manually via SSH.</p>";
    echo "</div></body></html>";
    exit;
}
step('exec() is available ✓');

// ── Check PHP binary ───────────────────────────────────────────────────────
$php = PHP_BINARY ?: 'php';
step("PHP binary: {$php}");

// ── Try system composer ────────────────────────────────────────────────────
$composerBin = null;
foreach (['composer', '/usr/local/bin/composer', '/usr/bin/composer'] as $c) {
    $r = runCmd($c . ' --version');
    if ($r['code'] === 0 && strpos($r['out'], 'Composer') !== false) {
        $composerBin = $c;
        step("Found system composer: {$c} ✓");
        break;
    }
}

// ── Download composer.phar if needed ──────────────────────────────────────
if (!$composerBin) {
    step('System composer not found — downloading composer.phar…', 'warn');

    if (file_exists($pharPath) && filesize($pharPath) > 100000) {
        step('composer.phar already present ✓');
    } else {
        $downloaded = false;
        if (function_exists('curl_init')) {
            $ch = curl_init('https://getcomposer.org/composer-stable.phar');
            $fp = fopen($pharPath, 'wb');
            curl_setopt_array($ch, [
                CURLOPT_FILE           => $fp,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_USERAGENT      => 'HandyCRM-Installer',
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $ok   = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);
            $downloaded = $ok && $code === 200 && file_exists($pharPath) && filesize($pharPath) > 100000;
        }

        if (!$downloaded) {
            // cURL failed — try file_get_contents
            $ctx  = stream_context_create(['http' => ['method' => 'GET', 'user_agent' => 'HandyCRM-Installer', 'timeout' => 120]]);
            $data = @file_get_contents('https://getcomposer.org/composer-stable.phar', false, $ctx);
            if ($data && strlen($data) > 100000) {
                file_put_contents($pharPath, $data);
                $downloaded = true;
            }
        }

        if (!$downloaded || !file_exists($pharPath)) {
            step('Failed to download composer.phar', 'err');
            echo "<p class='err'>Could not download composer.phar. Check that the server can reach the internet (cURL / allow_url_fopen).</p>";
            echo "</div></body></html>";
            exit;
        }

        step('composer.phar downloaded (' . round(filesize($pharPath) / 1024) . ' KB) ✓');
    }

    $composerBin = escapeshellarg($php) . ' ' . escapeshellarg($pharPath);
}

// ── Set COMPOSER_HOME ──────────────────────────────────────────────────────
$composerHome = sys_get_temp_dir() . '/composer_home_handycrm';
if (!is_dir($composerHome)) {
    mkdir($composerHome, 0755, true);
}
$env = 'COMPOSER_HOME=' . escapeshellarg($composerHome);

// ── Step 1: require smalot/pdfparser (updates composer.json + lock) ───────
// Note: "composer require" does not accept --no-dev
step('Running: composer require smalot/pdfparser --no-interaction …', 'warn');
$cmd1 = $env . ' ' . $composerBin
      . ' require smalot/pdfparser --no-interaction --prefer-dist'
      . ' --working-dir=' . escapeshellarg($rootDir);
$r1 = runCmd($cmd1);
echo "<pre>" . htmlspecialchars($r1['out']) . "</pre>\n";

// ── Step 2: update the lock file and install everything ───────────────────
// Use "update smalot/pdfparser" so the stale lock file gets regenerated
step('Running: composer update smalot/pdfparser --no-dev --no-interaction …', 'warn');
$cmd2 = $env . ' ' . $composerBin
      . ' update smalot/pdfparser --no-dev --no-interaction --prefer-dist'
      . ' --working-dir=' . escapeshellarg($rootDir);
$r2 = runCmd($cmd2);
echo "<pre>" . htmlspecialchars($r2['out']) . "</pre>\n";

$result = ['code' => ($r1['code'] === 0 || $r2['code'] === 0) ? 0 : 1,
           'out'  => $r1['out'] . "\n" . $r2['out']];

$success = $result['code'] === 0
    || strpos($result['out'], 'Nothing to install') !== false
    || strpos($result['out'], 'Generating autoload') !== false
    || strpos($result['out'], 'No packages') !== false;

if ($success) {
    step('Composer steps completed ✓', 'ok');
} else {
    step('Composer may have had errors (exit code ' . $result['code'] . ')', 'warn');
}

// ── Verify smalot/pdfparser ────────────────────────────────────────────────
$parserFile = $rootDir . '/vendor/smalot/pdfparser/src/Smalot/PdfParser/Parser.php';
if (file_exists($parserFile)) {
    step('smalot/pdfparser is installed ✓ — PDF scanning will now work!', 'ok');
} else {
    step('smalot/pdfparser NOT found in vendor/ — check errors above.', 'err');
}

// ── Cleanup composer.phar ──────────────────────────────────────────────────
if (file_exists($pharPath)) {
    @unlink($pharPath);
    step('Removed temporary composer.phar ✓');
}

?>
<hr style="border-color:#45475a;margin:2rem 0">
<p class="ok" style="font-size:1.1rem">✅ Done! Now <strong>delete this file</strong> from the server:</p>
<pre><?= htmlspecialchars($rootDir . '/run_composer_install.php') ?></pre>
<p>You can delete it via FTP/cPanel File Manager.</p>
</div></body></html>
