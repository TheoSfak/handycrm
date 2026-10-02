<?php
/**
 * One-time script to update phpoffice/phpspreadsheet to a patched version.
 * Fixes CVE-2026-40902, CVE-2026-40863, CVE-2026-34084, CVE-2026-40296, CVE-2026-35453
 *
 * 1. Upload this file to the server root (next to index.php).
 * 2. Visit:  https://yourdomain.com/run_composer_update.php?token=handycrm2026
 * 3. DELETE this file from the server when done.
 */

define('SECRET_TOKEN', 'handycrm2026');

if (($_GET['token'] ?? '') !== SECRET_TOKEN) {
    http_response_code(403);
    die('403 Forbidden — add ?token=handycrm2026 to the URL.');
}

$rootDir = __DIR__;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<title>HandyCRM — Composer Update</title>
<style>
  body{font-family:monospace;background:#1e1e2e;color:#cdd6f4;padding:2rem;margin:0}
  h2{color:#89b4fa} .ok{color:#a6e3a1} .err{color:#f38ba8} .warn{color:#fab387}
  pre{background:#181825;padding:1rem;border-radius:6px;white-space:pre-wrap;word-break:break-all}
  .box{max-width:860px;margin:auto}
  .step{margin:1.2rem 0;padding:.8rem 1rem;background:#181825;border-radius:6px;border-left:4px solid #89b4fa}
</style>
</head>
<body><div class="box">
<h2>🔒 HandyCRM — Security Update: phpoffice/phpspreadsheet</h2>
<p>Updating <strong>phpspreadsheet</strong> to fix 5 CVEs (including 1 critical SSRF/RCE)…</p>
<?php

function step(string $title, string $status = 'ok'): void {
    echo "<div class='step'><span class='{$status}'>[{$status}]</span> " . htmlspecialchars($title) . "</div>\n";
    flush();
}

function runCmd(string $cmd): array {
    $output = []; $code = 0;
    exec($cmd . ' 2>&1', $output, $code);
    return ['out' => implode("\n", $output), 'code' => $code];
}

// ── Check exec() ──────────────────────────────────────────────────────────
$disabled = array_map('trim', explode(',', ini_get('disable_functions') ?: ''));
if (in_array('exec', $disabled)) {
    step('exec() is disabled — cannot run composer', 'err');
    echo '</div></body></html>';
    exit;
}
step('exec() is available ✓');

// ── Find composer ─────────────────────────────────────────────────────────
$composerBin = null;
foreach (['composer', '/usr/local/bin/composer', '/usr/bin/composer'] as $bin) {
    $test = runCmd('which ' . escapeshellarg($bin));
    if ($test['code'] === 0 && trim($test['out']) !== '') {
        $composerBin = $bin;
        break;
    }
}
if (!$composerBin) {
    step('composer not found in PATH', 'err');
    echo '</div></body></html>';
    exit;
}
step('Found composer: ' . $composerBin . ' ✓');

// ── COMPOSER_HOME ─────────────────────────────────────────────────────────
$composerHome = sys_get_temp_dir() . '/composer_home_handycrm';
if (!is_dir($composerHome)) {
    mkdir($composerHome, 0755, true);
}
$env = 'COMPOSER_HOME=' . escapeshellarg($composerHome);

// ── Show current version ──────────────────────────────────────────────────
$lockFile = $rootDir . '/composer.lock';
$currentVersion = 'unknown';
if (file_exists($lockFile)) {
    $lock = json_decode(file_get_contents($lockFile), true);
    foreach (($lock['packages'] ?? []) as $pkg) {
        if ($pkg['name'] === 'phpoffice/phpspreadsheet') {
            $currentVersion = $pkg['version'];
            break;
        }
    }
}
step('Current phpspreadsheet version: ' . $currentVersion, 'warn');

// ── Run update ────────────────────────────────────────────────────────────
step('Running: composer update phpoffice/phpspreadsheet --no-dev --no-interaction …', 'warn');
$cmd = $env . ' ' . $composerBin
     . ' update phpoffice/phpspreadsheet --no-dev --no-interaction --prefer-dist'
     . ' --working-dir=' . escapeshellarg($rootDir);
$result = runCmd($cmd);
echo "<pre>" . htmlspecialchars($result['out']) . "</pre>\n";

// ── Show new version ──────────────────────────────────────────────────────
$newVersion = 'unknown';
if (file_exists($lockFile)) {
    $lock = json_decode(file_get_contents($lockFile), true);
    foreach (($lock['packages'] ?? []) as $pkg) {
        if ($pkg['name'] === 'phpoffice/phpspreadsheet') {
            $newVersion = $pkg['version'];
            break;
        }
    }
}

if ($result['code'] === 0 || strpos($result['out'], 'Generating autoload') !== false) {
    step('Update completed ✓ — new version: ' . $newVersion, 'ok');
} else {
    step('Update may have had errors (exit code ' . $result['code'] . ')', 'err');
}

// ── Verify fix ────────────────────────────────────────────────────────────
step('Running: composer audit …', 'warn');
$auditCmd = $env . ' ' . $composerBin . ' audit --working-dir=' . escapeshellarg($rootDir);
$audit = runCmd($auditCmd);
echo "<pre>" . htmlspecialchars($audit['out']) . "</pre>\n";

if ($audit['code'] === 0) {
    step('No security vulnerabilities remaining ✓', 'ok');
} else {
    step('Vulnerabilities may still exist — review audit output above', 'warn');
}
?>
<hr style="border-color:#45475a;margin:2rem 0">
<p class="warn">⚠️ <strong>Delete this file</strong> from the server after use!</p>
<pre>/run_composer_update.php</pre>
</div></body></html>
