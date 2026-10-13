<?php
/**
 * Application Configuration
 * Minister Improvement and Training Retreat
 *
 * When frontend and backend are on different hosts:
 *   - APP_URL      = the API / backend origin (used for CORS + absolute upload URLs)
 *   - FRONTEND_URL = the public frontend origin (add to allowed CORS origins)
 */

// Error reporting (disable in production)
$envFile = dirname(__DIR__, 2) . '/.env';
if (is_readable($envFile)) {
	foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		$line = trim($line);
		if ($line === '' || $line[0] === '#') {
			continue;
		}
		[$name, $value] = array_pad(explode('=', $line, 2), 2, '');
		$name = trim($name);
		$value = trim($value);
		if ($name === '') {
			continue;
		}
		if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
			$value = substr($value, 1, -1);
		}
		putenv($name . '=' . $value);
	}
}

function envValue(string $name, ?string $default = null): ?string
{
	$value = getenv($name);
	return $value === false ? $default : $value;
}

error_reporting(E_ALL);
ini_set('display_errors', envValue('APP_DISPLAY_ERRORS', '1'));

// Timezone
date_default_timezone_set('Africa/Lagos');

// Environment-aware URL detection
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
$isHttps = (
	(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
	|| (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
	|| $forwardedProto === 'https'
);
$scheme = $isHttps ? 'https' : 'http';

$hostOnly = strtolower((string)preg_replace('/:\d+$/', '', $httpHost));
$isLocalhost = in_array($hostOnly, ['localhost', '127.0.0.1', '::1'], true);

$detectedAppUrl = $isLocalhost
	? 'http://localhost/mitre2/api'
	: 'https://api.leadstar.com.ng/mitre';

$detectedFrontendUrl = $isLocalhost
	? 'http://localhost:5500,http://127.0.0.1:5500,http://localhost/mitre2/frontend'
	: 'https://mitre.com.ng';

// Database credentials are loaded from .env and have no committed fallback.
$dbHost = envValue('DB_HOST', 'localhost');
$dbName = envValue('DB_NAME', $isLocalhost ? 'training_school' : null);
$dbUser = envValue('DB_USER', $isLocalhost ? 'root' : null);
$dbPass = envValue('DB_PASS', '');

define('DB_HOST', $dbHost);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_CHARSET', 'utf8mb4');

// Application
define('APP_NAME', 'MITRE');
// Backend / API base URL (no trailing slash). Used for CORS and building absolute upload URLs.
define('APP_URL', rtrim(envValue('APP_URL', $detectedAppUrl), '/'));
// Frontend origin(s) allowed to call this API with credentials (comma-separated if multiple)
define('FRONTEND_URL', rtrim(envValue('FRONTEND_URL', $detectedFrontendUrl), '/'));
define('APP_VERSION', '1.0.0');

// Session
define('SESSION_NAME', 'MITRE_SESSION');
define('SESSION_LIFETIME', 259200);             // 3 days

// eBulkSMS gateway. Keep the username and API key server-side; never put them in public/.
define('SMS_ENABLED', filter_var(envValue('SMS_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN));
define('SMS_USERNAME', envValue('SMS_USERNAME', ''));
define('SMS_API_KEY', envValue('SMS_API_KEY', ''));
define('SMS_SENDER_ID', envValue('SMS_SENDER_ID', 'MITRE'));
define('SMS_API_URL', envValue('SMS_API_URL', 'https://api.ebulksms.com/sendsms.json'));

// File uploads – live under the API host
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', rtrim(APP_URL, '/') . '/uploads/');  // stored in DB as full URL
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);     // 5 MB

// Scoring weights (do not change without updating business logic)
define('WEIGHT_ATTENDANCE', 45);
define('WEIGHT_SUMMARY', 5);
define('WEIGHT_SHORT_PAPER', 5);
define('WEIGHT_LONG_PAPER', 10);
define('WEIGHT_TERM_PAPER', 30);
define('WEIGHT_OVERSIGHT', 5);                  // Extra 5 marks

// Conclave rules
define('CONCLAVES_PER_YEAR', 2);
define('TOTAL_CONCLAVES', 6);
define('ATTENDANCE_DAYS', 3);
define('ATTENDANCE_SESSIONS', ['morning', 'evening']);

// Application window
define('APPLICATION_CLOSE_DAYS_BEFORE', 30);    // 1 month before resumption
define('ADMISSION_SMS_DAYS_BEFORE', 14);        // 2 weeks before resumption
