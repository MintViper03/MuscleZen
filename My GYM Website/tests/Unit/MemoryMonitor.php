<?php
class MemoryMonitor {
    private static $instance = null;
    private $snapshots = [];
    private $maxMemoryLimit;

    private function __construct() {
        $this->maxMemoryLimit = ini_get('memory_limit');
        $this->takeSnapshot('init');
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new MemoryMonitor();
        }
        return self::$instance;
    }

    public function takeSnapshot($label) {
        $this->snapshots[$label] = [
            'memory_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(true),
            'timestamp' => microtime(true)
        ];
    }

    public function getMemoryUsage() {
        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = $this->getMemoryLimitBytes();

        return [
            'current' => $this->formatBytes($current),
            'peak' => $this->formatBytes($peak),
            'limit' => $this->formatBytes($limit),
            'percentage' => round(($current / $limit) * 100, 2)
        ];
    }

    public function getSnapshots() {
        $formatted = [];
        $initial = reset($this->snapshots);

        foreach ($this->snapshots as $label => $snapshot) {
            $formatted[$label] = [
                'memory' => $this->formatBytes($snapshot['memory_usage']),
                'peak' => $this->formatBytes($snapshot['peak_usage']),
                'difference' => $this->formatBytes($snapshot['memory_usage'] - $initial['memory_usage']),
                'time_elapsed' => round($snapshot['timestamp'] - $initial['timestamp'], 4)
            ];
        }

        return $formatted;
    }

    public function checkMemoryLimit($threshold = 0.9) {
        $usage = memory_get_usage(true);
        $limit = $this->getMemoryLimitBytes();
        
        if (($usage / $limit) > $threshold) {
            $message = sprintf(
                "Memory usage warning: %s of %s (%d%%)",
                $this->formatBytes($usage),
                $this->formatBytes($limit),
                round(($usage / $limit) * 100)
            );
            Logger::warning($message);
            
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }

    private function getMemoryLimitBytes() {
        $limit = $this->maxMemoryLimit;
        $unit = strtolower(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);
        
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }

    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function generateReport() {
        $report = "Memory Usage Report\n";
        $report .= "==================\n\n";
        
        $usage = $this->getMemoryUsage();
        $report .= sprintf("Current Usage: %s\n", $usage['current']);
        $report .= sprintf("Peak Usage: %s\n", $usage['peak']);
        $report .= sprintf("Memory Limit: %s\n", $usage['limit']);
        $report .= sprintf("Usage Percentage: %.2f%%\n", $usage['percentage']);
        
        $report .= "\nSnapshots:\n";
        foreach ($this->getSnapshots() as $label => $snapshot) {
            $report .= sprintf(
                "%s:\n  Memory: %s\n  Peak: %s\n  Difference: %s\n  Time: %.4fs\n",
                $label,
                $snapshot['memory'],
                $snapshot['peak'],
                $snapshot['difference'],
                $snapshot['time_elapsed']
            );
        }

        return $report;
    }
}
