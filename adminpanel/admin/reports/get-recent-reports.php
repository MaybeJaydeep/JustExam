<?php
/**
 * Get Recent Reports API Endpoint
 * Returns list of recently generated reports
 */

session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Check authentication
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    sendJSON(['error' => 'Unauthorized'], 401);
}

if (!checkSessionTimeout()) {
    session_destroy();
    sendJSON(['error' => 'Session expired'], 401);
}

try {
    $reportsDir = __DIR__ . '/generated/';
    $reports = [];
    
    if (is_dir($reportsDir)) {
        $files = scandir($reportsDir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filepath = $reportsDir . $file;
            if (is_file($filepath)) {
                $fileInfo = pathinfo($file);
                $fileStats = stat($filepath);
                
                // Parse filename to extract report type
                $nameParts = explode('_', $fileInfo['filename']);
                $reportType = isset($nameParts[0]) ? $nameParts[0] : 'unknown';
                
                $reports[] = [
                    'type' => $reportType,
                    'format' => $fileInfo['extension'],
                    'filename' => $file,
                    'file_url' => 'reports/generated/' . $file,
                    'size' => formatFileSize($fileStats['size']),
                    'generated_at' => date('Y-m-d H:i:s', $fileStats['mtime'])
                ];
            }
        }
        
        // Sort by modification time (newest first)
        usort($reports, function($a, $b) {
            return strtotime($b['generated_at']) - strtotime($a['generated_at']);
        });
        
        // Limit to last 10 reports
        $reports = array_slice($reports, 0, 10);
    }
    
    header('Content-Type: application/json');
    echo json_encode($reports);
    
} catch (Exception $e) {
    error_log("Get Recent Reports Error: " . $e->getMessage());
    sendJSON(['error' => 'Failed to load recent reports'], 500);
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>