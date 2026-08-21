<?php
// Database Configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'nova_news';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// 1. Root News Directory သတ်မှတ်ခြင်း
$baseDir = __DIR__ . DIRECTORY_SEPARATOR . 'News';
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0777, true);
}

// 2. Posts များကို Category Name ဖြင့် ချိတ်ယူခြင်း
$query = "SELECT p.*, c.name AS category_name 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$successCount = 0;

while ($row = mysqli_fetch_assoc($result)) {
    // Category Folder အမည် ရှင်းလင်းသတ်မှတ်ခြင်း
    $catName = !empty($row['category_name']) ? trim($row['category_name']) : 'Uncategorized';
    // Folder name အတွက် Safe ဖြစ်သော စာလုံးသာ ယူမည်
    $safeCat = preg_replace('/[\\\\\/:\*\?"<>\|]/', '_', $catName);
    
    $categoryFolder = $baseDir . DIRECTORY_SEPARATOR . $safeCat;
    
    // Folder မရှိပါက ဆောက်ပေးမည် (recursive: true)
    if (!is_dir($categoryFolder)) {
        mkdir($categoryFolder, 0777, true);
    }

    // File Name အဖြစ် ID ကို အခြေခံပြီး Safe ဖြစ်အောင် ပြုလုပ်ခြင်း (Windows Error မတက်စေရန်)
    $safeSlug = preg_replace('/[\\\\\/:\*\?"<>\|\s]/', '_', $row['slug'] ?? '');
    // Slug အလွန်ရှည်ပါက ၅၀ လုံးသာ ဖြတ်ယူမည်
    $shortSlug = mb_substr($safeSlug, 0, 50, 'UTF-8');
    
    $fileName = 'post_' . $row['id'] . (!empty($shortSlug) ? '_' . $shortSlug : '') . '.txt';
    $filePath = $categoryFolder . DIRECTORY_SEPARATOR . $fileName;

    // File Content တည်ဆောက်ခြင်း
    $data  = "Title: " . ($row['title'] ?? '') . "\n";
    $data .= "Slug: " . ($row['slug'] ?? '') . "\n";
    $data .= "Type: " . ($row['type'] ?? 'Free') . "\n";
    $data .= "Category: " . $catName . "\n";
    $data .= "Created At: " . ($row['created_at'] ?? '') . "\n";
    $data .= "--------------------------------------------------\n\n";
    $data .= ($row['content'] ?? '');

    // UTF-8 BOM ထည့်သွင်း၍ ဖိုင်ရေးခြင်း
    file_put_contents($filePath, "\xEF\xBB\xBF" . $data);
    $successCount++;
}

echo "<div style='font-family: Arial; padding: 20px; color: green;'>";
echo "<h2>✅ Export Successful!</h2>";
echo "<p>Total <strong>{$successCount}</strong> posts exported to <strong>" . htmlspecialchars($baseDir) . "</strong> folder.</p>";
echo "</div>";
?>