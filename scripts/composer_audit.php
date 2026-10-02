<?php
/**
 * One-time Composer audit helper.
 *
 * 1. Upload this file to the server root (next to index.php).
 * 2. Visit:  https://yourdomain.com/run_composer_audit.php?token=handycrm2026
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
<title>HandyCRM — Composer Audit</title>
<style>
  body{font-family:monospace;background:#1e1e2e;color:#cdd6f4;padding:2rem;margin:0}
  h2{color:#89b4fa} .ok{color:#a6e3a1} .err{color:#f38ba8} .warn{color:#fab387}
  pre{background:#181825;padding:1rem;border-radius:6px;white-space:pre-wrap;word-break:break-all}
  .box{max-width:860px;margin:auto}
  .step{margin:1.2rem 0;padding:.8rem 1rem;background:#181825;border-radius:6px;border-left:4px solid #89b4fa}
</style>
</head>
<body><div class="box">
<h2>🔍 HandyCRM — Composer Security Audit</h2>
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

// ── Set COMPOSER_HOME ─────────────────────────────────────────────────────
$composerHome = sys_get_temp_dir() . '/composer_home_handycrm';
if (!is_dir($composerHome)) {
    mkdir($composerHome, 0755, true);
}
$env = 'COMPOSER_HOME=' . escapeshellarg($composerHome);

// ── Run audit ─────────────────────────────────────────────────────────────
step('Running: composer audit …', 'warn');
$cmd = $env . ' ' . $composerBin
     . ' audit'
     . ' --working-dir=' . escapeshellarg($rootDir);
$result = runCmd($cmd);

echo "<pre>" . htmlspecialchars($result['out']) . "</pre>\n";

if ($result['code'] === 0) {
    step('No security vulnerabilities found ✓', 'ok');
} else {
    step('Vulnerabilities found — review output above', 'warn');
}
?>
<hr style="border-color:#45475a;margin:2rem 0">
<p class="warn">⚠️ <strong>Delete this file</strong> from the server after use!</p>
<pre>/run_composer_audit.php</pre>
</div></body></html>
