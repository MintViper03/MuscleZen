<?php
class QueryProfiler {
    private static $queries = [];
    private static $slowQueryThreshold = 1.0; // seconds

    public static function startQuery($sql, $params = []) {
        $queryId = uniqid();
        self::$queries[$queryId] = [
            'sql' => $sql,
            'params' => $params,
            'start_time' => microtime(true),
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];
        return $queryId;
    }

    public static function endQuery($queryId) {
        if (isset(self::$queries[$queryId])) {
            self::$queries[$queryId]['end_time'] = microtime(true);
            self::$queries[$queryId]['duration'] = 
                self::$queries[$queryId]['end_time'] - 
                self::$queries[$queryId]['start_time'];

            // Log slow queries
            if (self::$queries[$queryId]['duration'] > self::$slowQueryThreshold) {
                self::logSlowQuery($queryId);
            }
        }
    }

    private static function logSlowQuery($queryId) {
        $query = self::$queries[$queryId];
        $message = sprintf(
            "Slow Query (%.4fs):\nSQL: %s\nParams: %s\nTrace:\n%s",
            $query['duration'],
            $query['sql'],
            json_encode($query['params']),
            self::formatStackTrace($query['stack_trace'])
        );
        Logger::warning($message);
    }

    private static function formatStackTrace($trace) {
        $formatted = [];
        foreach ($trace as $item) {
            $formatted[] = sprintf(
                "%s:%d %s%s%s()",
                $item['file'] ?? 'unknown',
                $item['line'] ?? 0,
                $item['class'] ?? '',
                $item['type'] ?? '',
                $item['function'] ?? ''
            );
        }
        return implode("\n", $formatted);
    }

    public static function getQueryStats() {
        $stats = [
            'total_queries' => count(self::$queries),
            'total_time' => 0,
            'avg_time' => 0,
            'slow_queries' => 0
        ];

        foreach (self::$queries as $query) {
            if (isset($query['duration'])) {
                $stats['total_time'] += $query['duration'];
                if ($query['duration'] > self::$slowQueryThreshold) {
                    $stats['slow_queries']++;
                }
            }
        }

        $stats['avg_time'] = $stats['total_queries'] > 0 
            ? $stats['total_time'] / $stats['total_queries'] 
            : 0;

        return $stats;
    }
}
