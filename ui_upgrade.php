<?php
$files = glob(__DIR__ . '/admin/*.php');

foreach ($files as $file) {
    if (basename($file) === 'export-excel.php' || basename($file) === 'notification-action.php') {
        continue;
    }

    $content = file_get_contents($file);

    // Update Cards
    $content = str_replace(
        'bg-white rounded-xl shadow',
        'bg-white rounded-2xl shadow-sm border border-slate-200/60',
        $content
    );

    // Update Table Headers
    $content = str_replace(
        'bg-gray-50 text-left text-sm font-semibold text-gray-600',
        'bg-slate-50/50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider',
        $content
    );

    // Update Table Rows
    $content = str_replace(
        'hover:bg-gray-50',
        'hover:bg-slate-50/80 transition-colors',
        $content
    );

    // Update Form Inputs & Textareas
    $content = str_replace(
        'rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
        'rounded-xl border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all',
        $content
    );
    
    // Update Action Buttons (Text + Icons)
    $content = preg_replace(
        '/px-4 py-2 bg-blue-600 text-white rounded-lg(.*?)/',
        'px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm shadow-sm transition-all$1',
        $content
    );

    // Icon only buttons (edit)
    $content = preg_replace(
        '/text-blue-600 hover:text-blue-800(.*?)"(.*?)title="Edit"/',
        'text-blue-500 hover:text-blue-700 hover:bg-blue-50 p-1.5 rounded-lg transition-colors$1"$2title="Edit"',
        $content
    );

    // Icon only buttons (delete)
    $content = preg_replace(
        '/text-red-600 hover:text-red-800(.*?)"(.*?)title="Delete"/',
        'text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded-lg transition-colors$1"$2title="Delete"',
        $content
    );

    file_put_contents($file, $content);
    echo "Upgraded UI in: " . basename($file) . "\n";
}
echo "Done!\n";
