<?php
echo "<pre>";
$tempDir = "/home/noman/Desktop/AI/APK_PROJECT/storage/app/public/temp";
$files = glob("$tempDir/*.apk");
if (empty($files)) {
    die("No APK files found in $tempDir. Please upload one via the UI first.");
}
$apk = $files[0];
echo "Testing with APK: " . basename($apk) . "\n";

$aapt = "/usr/bin/aapt";
$cmd = "$aapt dump badging " . escapeshellarg($apk) . " 2>&1";
echo "Executing: $cmd\n";
$output = shell_exec($cmd);

if (!$output) {
    echo "ERROR: shell_exec returned nothing.\n";
} else {
    echo "Output length: " . strlen($output) . " bytes\n";
    
    if (preg_match("/application-label:'([^']+)'/", $output, $matches)) {
        echo "Found App Name: " . $matches[1] . "\n";
    } else {
        echo "Regex for App Name FAILED.\n";
        // Show first 1000 characters of output to see what's there
        echo "First 500 chars of output:\n" . substr($output, 0, 500) . "\n";
    }

    if (preg_match_all("/application-icon-(\d+):'([^']+)'/", $output, $matches, PREG_SET_ORDER)) {
        echo "Found " . count($matches) . " icons.\n";
        $bestIcon = end($matches);
        echo "Best icon: " . $bestIcon[2] . " (Size: " . $bestIcon[1] . ")\n";
        
        $unzip = "/usr/bin/unzip";
        $testExtract = "/home/noman/Desktop/AI/APK_PROJECT/storage/app/public/temp/test_extract.png";
        $unzipCmd = "$unzip -p " . escapeshellarg($apk) . " " . escapeshellarg($bestIcon[2]) . " > " . escapeshellarg($testExtract);
        echo "Executing unzip: $unzipCmd\n";
        shell_exec($unzipCmd);
        
        if (file_exists($testExtract)) {
            echo "SUCCESS: Icon extracted. Size: " . filesize($testExtract) . " bytes\n";
            @unlink($testExtract);
        } else {
            echo "FAILURE: Icon not extracted.\n";
        }
    } else {
        echo "Regex for Icons FAILED.\n";
    }
}

echo "\nWhoami: " . shell_exec('whoami');
echo "PHP Version: " . phpversion();
echo "</pre>";
