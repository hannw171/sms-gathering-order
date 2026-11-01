/// Global application configuration constants
abstract final class AppConfig {
  static const String appName = 'SMS Gathering Order';
  static const String appVersion = '1.0.0';
  static const String companyName = 'PT. Surya Multi Sentosa';

  /// Default API base URL (override via --dart-define=API_BASE_URL=...)
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://api.smsgathering.id/api/v1',
  );

  /// Enable mock data fallback during development / offline demo
  static const bool useMockFallback = bool.fromEnvironment(
    'USE_MOCK_FALLBACK',
    defaultValue: true,
  );

  static const Duration connectTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);
}

