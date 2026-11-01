import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../data/models/order_model.dart';

/// Card displaying the guest pharmacy's current Leaderboard Rank & Unlocked Reward Milestone
/// (Figma Node: 1:1330, 1:1782, 1:1888)
class RankMilestoneCard extends StatelessWidget {
  final OrderModel order;

  const RankMilestoneCard({
    super.key,
    required this.order,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    return Container(
      width: double.infinity,
      padding: AppMetrics.paddingCard,
      decoration: BoxDecoration(
        color: c.actionSecondaryContainer.withValues(alpha: 0.55),
        borderRadius: AppMetrics.roundedXl,
        border: Border.all(color: c.actionPrimary.withValues(alpha: 0.25)),
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(
              horizontal: AppMetrics.space3,
              vertical: AppMetrics.space1,
            ),
            decoration: BoxDecoration(
              color: c.rewardAccent,
              borderRadius: AppMetrics.roundedFull,
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.workspace_premium_rounded,
                    size: 16, color: c.rewardOnAccent),
                const SizedBox(width: AppMetrics.space1),
                Text(
                  'PERINGKAT SEMENTARA #${order.currentLeaderboardRank}',
                  style: AppTypography.labelLarge(color: c.rewardOnAccent),
                ),
              ],
            ),
          ),
          const SizedBox(height: AppMetrics.space3),
          Text(
            order.unlockedRewardTitle != null
                ? 'Reward Terkunci: ${order.unlockedRewardTitle}'
                : 'Terus tingkatkan transaksi untuk membuka tier hadiah emas!',
            style: AppTypography.titleMedium(color: c.textPrimary),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: AppMetrics.space1),
          Text(
            'Pesanan Anda telah masuk ke antrean verifikasi dan dihitung pada papan peringkat Most Spender.',
            style: AppTypography.bodySmall(color: c.textSecondary),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

