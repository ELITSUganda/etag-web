<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Guardian Enabled
    |--------------------------------------------------------------------------
    */
    'enabled' => env('GUARDIAN_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Request Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'exclude_paths' => [
            '#^/admin/guardian/api/#',
            '#^/_debugbar#',
            '#^/favicon\.ico$#',
            '#^/vendor/#',
        ],
        'retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (per IP)
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'requests_per_minute' => 120,
        'requests_per_hour' => 3000,
        'block_duration_minutes' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Spike Detection
    |--------------------------------------------------------------------------
    */
    'spike_detection' => [
        'requests_per_minute_threshold' => 500,
        'single_ip_per_minute_threshold' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Heavy Endpoint Detection
    |--------------------------------------------------------------------------
    */
    'heavy_endpoints' => [
        'slow_threshold_ms' => 3000,
        'memory_threshold_mb' => 64,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bad Bot Detection
    |--------------------------------------------------------------------------
    */
    'bad_bots' => [
        'suspicious_agents' => [
            '/sqlmap/i',
            '/nikto/i',
            '/nmap/i',
            '/masscan/i',
            '/dirbuster/i',
            '/gobuster/i',
            '/wpscan/i',
            '/havij/i',
            '/acunetix/i',
        ],
        'max_404_per_10_min' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | System Resource Thresholds
    |--------------------------------------------------------------------------
    */
    'resource_thresholds' => [
        'cpu_load_warning' => 4.0,
        'cpu_load_critical' => 8.0,
        'memory_warning_percent' => 80,
        'memory_critical_percent' => 95,
        'mysql_connections_warning' => 100,
        'mysql_connections_critical' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Honeypot Paths
    |--------------------------------------------------------------------------
    | Paths no legitimate user would access. Any IP hitting these
    | is instantly blocked. Great for catching automated scanners.
    |--------------------------------------------------------------------------
    */
    'honeypot_paths' => [
        '/wp-admin',
        '/wp-login.php',
        '/wp-content',
        '/xmlrpc.php',
        '/.env',
        '/phpmyadmin',
        '/pma',
        '/phpinfo.php',
        '/shell.php',
        '/cmd.php',
        '/.git',
        '/.svn',
        '/backup',
        '/db.sql',
        '/database.sql',
        '/config.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist (never blocked, never logged)
    |--------------------------------------------------------------------------
    */
    'ip_whitelist' => array_merge([
        '127.0.0.1',
        '::1',
    ], array_filter(explode(',', env('GUARDIAN_IP_WHITELIST', '')))),

    /*
    |--------------------------------------------------------------------------
    | Cleanup
    |--------------------------------------------------------------------------
    */
    'cleanup' => [
        'logs_older_than_days' => 30,
        'alerts_older_than_days' => 90,
        'purge_expired_blocks' => true,
    ],

];
