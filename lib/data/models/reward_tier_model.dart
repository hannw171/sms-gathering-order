/// Model representing a Gamification Reward Tier
class RewardTierModel {
  final String id;
  final int tierLevel;
  final String title;
  final String rewardDescription;
  final int minSpend;

  const RewardTierModel({
    required this.id,
    required this.tierLevel,
    required this.title,
    required this.rewardDescription,
    required this.minSpend,
  });

  factory RewardTierModel.fromJson(Map<String, dynamic> json) {
    return RewardTierModel(
      id: json['id']?.toString() ?? '',
      tierLevel: (json['tier_level'] as num?)?.toInt() ?? 1,
      title: json['title']?.toString() ?? '',
      rewardDescription: json['reward_description']?.toString() ?? '',
      minSpend: (json['min_spend'] as num?)?.toInt() ?? 0,
    );
  }
}

