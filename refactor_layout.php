<?php
$files = glob(__DIR__ . '/admin/*.php');

foreach ($files as $file) {
    if (basename($file) === 'export-excel.php' || basename($file) === 'notification-action.php') {
        continue; // Skip non-HTML files
    }

    $content = file_get_contents($file);
    
    // 1. Replace Sidebar
    $content = preg_replace('/<!-- Sidebar -->\s*/is', '', $content);
    $content = preg_replace('/<aside class="fixed.*?<\/aside>/is', "<?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>", $content);

    // 2. Replace Header and Extract Title
    if (preg_match('/<header[^>]*>.*?<h2[^>]*>(.*?)<\/h2>.*?<\/header>/is', $content, $matches)) {
        $title = trim(strip_tags($matches[1])); // get inner text, remove any spans just in case
        $titleReplacement = "<?php \$pageTitle = '" . addslashes($title) . "'; include __DIR__ . '/../includes/admin-topbar.php'; ?>";
        
        // Remove '<!-- Header -->' if exists
        $content = preg_replace('/<!-- Header -->\s*/is', '', $content);
        $content = preg_replace('/<header[^>]*>.*?<\/header>/is', $titleReplacement, $content);
    }
    
    // 3. Optional: Some simple form/table class modernizations across all files
    // But let's just stick to layout first to be safe!
    
    file_put_contents($file, $content);
    echo "Refactored layout in: " . basename($file) . "\n";
}
echo "Done!\n";
