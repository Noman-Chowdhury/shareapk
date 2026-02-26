<?php
echo "<pre>";
$apkPath = "/home/noman/Desktop/AI/APK_PROJECT/storage/app/public/temp";
$files = glob("$apkPath/*.apk");
if (empty($files)) {
    die("No APK files found in $apkPath");
}
$apk = $files[0];
echo "Testing with APK: $apk\n";

$aapt = "/usr/bin/aapt";
$cmd = "$aapt dump badging " . escapeshellarg($apk) . " 2>&1 | grep -E 'application-label:|package:'";
echo "Command: $cmd\n";
$output = shell_exec($cmd);
echo "Output:\n" . ($output ?: "(empty)") . "\n";

$whoami = shell_exec('whoami');
echo "Running as user: $whoami\n";

$iconsDir = "/home/noman/Desktop/AI/APK_PROJECT/storage/app/public/icons";
echo "Is icons dir writable? " . (is_writable($iconsDir) ? "YES" : "NO") . "\n";
echo "</pre>";
