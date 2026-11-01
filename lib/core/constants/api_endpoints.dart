/// REST API Endpoint definitions for SMS Gathering Platform
abstract final class ApiEndpoints {
  // Guest / Pharmacy Onboarding
  static const String pharmacies = '/pharmacies';
  static const String claimSession = '/sessions/claim';
  static const String releaseSession = '/sessions/release';

  // Catalog & Principals
  static const String principals = '/principals';
  static const String products = '/products';
  static const String rewardTiers = '/rewards/tiers';

  // Orders & Cart Submission
  static const String orders = '/orders';
  static String orderDetail(String id) => '/orders/$id';

  // Live Leaderboard & Stage Display
  static const String leaderboard = '/leaderboard';
  static const String stageStatus = '/stage/status';
}

