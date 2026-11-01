import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../data/models/leaderboard_entry_model.dart';

/// Standard Leaderboard Row for Rank 4+ (DESIGN.md Section 11.2)
class RankListTile extends StatelessWidget {
  final LeaderboardEntryModel entry;

  const RankListTile({
    super.key,
    required this.entry,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    return Container(
      padding: AppMetrics.paddingCard,
      decoration: BoxDecoration(
        color: c.surfaceDefault,
        borderRadius: AppMetrics.roundedLg,
        border: Border.all(color: c.borderDecorative),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: c.surfaceSubtle,
              borderRadius: AppMetrics.roundedFull,
              border: Border.all(color: c.borderDecorative),
            ),
            alignment: Alignment.center,
            child: Text(
              '#${entry.rank}',
              style: AppTypography.labelLarge(color: c.textSecondary),
            ),
          ),
          const SizedBox(width: AppMetrics.space3),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  entry.pharmacyName,
                  style: AppTypography.titleMedium(color: c.textPrimary),
                ),
                Text(
                  '${entry.city} • ${entry.totalOrders} Order',
                  style: AppTypography.bodySmall(color: c.textMuted),
                ),
              ],
            ),
          ),
          if (entry.isMovingUp)
            Padding(
              padding: const EdgeInsets.only(right: AppMetrics.space2),
              child: Icon(
                Icons.arrow_upward_rounded,
                size: 16,
                color: c.statusSuccess,
              ),
            ),
          Text(
            CurrencyFormatter.format(entry.totalSpend),
            style: AppTypography.priceMedium(color: c.textPrimary),
          ),
        ],
      ),
    );
  }
}

