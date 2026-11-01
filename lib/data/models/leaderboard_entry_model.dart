/// Model representing a Pharmacy Rank Entry on the Most Spender Leaderboard
class LeaderboardEntryModel {
  final int rank;
  final int? previousRank;
  final String pharmacyId;
  final String pharmacyName;
  final String city;
  final int totalSpend;
  final int totalOrders;
  final String? unlockedTierBadge;

  const LeaderboardEntryModel({
    required this.rank,
    this.previousRank,
    required this.pharmacyId,
    required this.pharmacyName,
    required this.city,
    required this.totalSpend,
    this.totalOrders = 1,
    this.unlockedTierBadge,
  });

  bool get isTopThree => rank >= 1 && rank <= 3;
  bool get isMovingUp => previousRank != null && rank < previousRank!;

  factory LeaderboardEntryModel.fromJson(Map<String, dynamic> json) {
    return LeaderboardEntryModel(
      rank: (json['rank'] as num?)?.toInt() ?? 0,
      previousRank: (json['previous_rank'] as num?)?.toInt(),
      pharmacyId: json['pharmacy_id']?.toString() ?? '',
      pharmacyName: json['pharmacy_name']?.toString() ?? '',
      city: json['city']?.toString() ?? '',
      totalSpend: (json['total_spend'] as num?)?.toInt() ?? 0,
      totalOrders: (json['total_orders'] as num?)?.toInt() ?? 1,
      unlockedTierBadge: json['unlocked_tier_badge']?.toString(),
    );
  }
}

