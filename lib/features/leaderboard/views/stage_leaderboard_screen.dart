import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../providers/leaderboard_provider.dart';

/// Big Screen Stage Projector Display (16:9 Ballroom Mode)
/// Conforms to Figma Node 1:1435 & DESIGN.md Section 1.3 / 2.3
class StageLeaderboardScreen extends StatelessWidget {
  const StageLeaderboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<LeaderboardProvider>();

    return Theme(
      data: AppTheme.darkTheme,
      child: Builder(
        builder: (context) {
          final c = context.colors;
          return Scaffold(
            backgroundColor: const Color(0xFF090D16), // Stage Projector Base
            appBar: AppBar(
              backgroundColor: Colors.transparent,
              title: Text(
                'LIVE STAGE DISPLAY — MOST SPENDER LEADERBOARD',
                style: AppTypography.titleLarge(color: c.rewardAccent),
              ),
              actions: [
                IconButton(
                  icon: const Icon(Icons.refresh_rounded, color: Colors.white),
                  onPressed: provider.refreshLeaderboard,
                ),
              ],
            ),
            body: Padding(
              padding: const EdgeInsets.all(AppMetrics.space6),
              child: ListView.separated(
                itemCount: provider.entries.length,
                separatorBuilder: (_, _) =>
                    const SizedBox(height: AppMetrics.space3),
                itemBuilder: (context, index) {
                  final entry = provider.entries[index];
                  final isFirst = entry.rank == 1;
                  return Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppMetrics.space6,
                      vertical: AppMetrics.space4,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E293B).withValues(alpha: 0.85),
                      borderRadius: AppMetrics.rounded2xl,
                      border: Border.all(
                        color: isFirst
                            ? const Color(0xFFF59E0B)
                            : Colors.white.withValues(alpha: 0.12),
                        width: isFirst ? 2.0 : 1.0,
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 48,
                          height: 48,
                          decoration: BoxDecoration(
                            color: isFirst
                                ? const Color(0xFFF59E0B)
                                : const Color(0xFF334155),
                            shape: BoxShape.circle,
                          ),
                          alignment: Alignment.center,
                          child: Text(
                            '#${entry.rank}',
                            style: AppTypography.titleLarge(
                              color: isFirst
                                  ? const Color(0xFF090D16)
                                  : Colors.white,
                            ),
                          ),
                        ),
                        const SizedBox(width: AppMetrics.space4),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                entry.pharmacyName,
                                style: AppTypography.headlineSmall(
                                  color: Colors.white,
                                ),
                              ),
                              Text(
                                '${entry.city} • ${entry.unlockedTierBadge ?? ""}',
                                style: AppTypography.bodyMedium(
                                  color: const Color(0xFF94A3B8),
                                ),
                              ),
                            ],
                          ),
                        ),
                        Text(
                          CurrencyFormatter.format(entry.totalSpend),
                          style: AppTypography.headlineSmall(
                            color: const Color(0xFFFCD34D), // Glowing Gold
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          );
        },
      ),
    );
  }
}

