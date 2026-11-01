import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../data/models/leaderboard_entry_model.dart';

/// Top 1, 2, 3 Podium Highlight Card (DESIGN.md Section 11.2 & Figma 1:2098)
class PodiumCard extends StatelessWidget {
  final LeaderboardEntryModel entry;

  const PodiumCard({
    super.key,
    required this.entry,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;
    final isFirst = entry.rank == 1;

    return Container(
      padding: AppMetrics.paddingCard,
      decoration: BoxDecoration(
        gradient: isFirst
            ? const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFFFEF3C7), Color(0xFFFDE68A)],
              )
            : null,
        color: isFirst ? null : c.surfaceDefault,
        borderRadius: AppMetrics.roundedXl,
        border: Border.all(
          color: isFirst ? const Color(0xFFF59E0B) : c.borderDecorative,
          width: isFirst ? 2.0 : 1.0,
        ),
        boxShadow: isFirst ? AppMetrics.shadowMd : AppMetrics.shadowSm,
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: isFirst ? const Color(0xFFF59E0B) : c.actionPrimary,
              shape: BoxShape.circle,
            ),
            alignment: Alignment.center,
            child: Text(
              '#${entry.rank}',
              style: AppTypography.titleLarge(color: Colors.white),
            ),
          ),
          const SizedBox(width: AppMetrics.space3),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    if (isFirst) ...[
                      const Icon(
                        Icons.emoji_events_rounded,
                        size: 18,
                        color: Color(0xFFB45309),
                      ),
                      const SizedBox(width: 4),
                    ],
                    Expanded(
                      child: Text(
                        entry.pharmacyName,
                        style: AppTypography.titleMedium(
                          color: isFirst
                              ? const Color(0xFF451A03)
                              : c.textPrimary,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  '${entry.city} • ${entry.unlockedTierBadge ?? "Participant"}',
                  style: AppTypography.bodySmall(
                    color: isFirst
                        ? const Color(0xFF92400E)
                        : c.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: AppMetrics.space2),
          Text(
            CurrencyFormatter.formatCompact(entry.totalSpend),
            style: AppTypography.priceMedium(
              color: isFirst ? const Color(0xFFB45309) : c.actionPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

