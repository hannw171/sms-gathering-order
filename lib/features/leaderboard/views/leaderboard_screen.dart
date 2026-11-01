import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/date_formatter.dart';
import '../providers/leaderboard_provider.dart';
import '../widgets/podium_card.dart';
import '../widgets/rank_list_tile.dart';
import 'stage_leaderboard_screen.dart';

/// Mobile Guest Most Spender Leaderboard Screen (Figma Node: 1:2098 & 1:2341)
class LeaderboardScreen extends StatelessWidget {
  const LeaderboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final c = context.colors;
    final provider = context.watch<LeaderboardProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Most Spender Leaderboard',
              style: AppTypography.titleLarge(color: c.textPrimary),
            ),
            Text(
              'Update: ${DateFormatter.formatTime(provider.lastUpdated)} WIB',
              style: AppTypography.caption(color: c.textMuted),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Mode Layar Panggung (Stage Display)',
            icon: Icon(Icons.tv_rounded, color: c.actionPrimary),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => const StageLeaderboardScreen(),
                ),
              );
            },
          ),
          IconButton(
            tooltip: 'Muat Ulang',
            icon: const Icon(Icons.refresh_rounded),
            onPressed: provider.refreshLeaderboard,
          ),
        ],
      ),
      body: provider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: provider.refreshLeaderboard,
              child: ListView(
                padding: AppMetrics.paddingScreen,
                children: [
                  Text(
                    'TOP 3 PODIUM UTAMA',
                    style: AppTypography.labelLarge(color: c.textSecondary),
                  ),
                  const SizedBox(height: AppMetrics.space3),
                  ...provider.topThree.map(
                    (entry) => Padding(
                      padding: const EdgeInsets.only(bottom: AppMetrics.space3),
                      child: PodiumCard(entry: entry),
                    ),
                  ),
                  const SizedBox(height: AppMetrics.space4),
                  Text(
                    'PERINGKAT APOTEK LAINNYA',
                    style: AppTypography.labelLarge(color: c.textSecondary),
                  ),
                  const SizedBox(height: AppMetrics.space3),
                  ...provider.runnerUps.map(
                    (entry) => Padding(
                      padding: const EdgeInsets.only(bottom: AppMetrics.space2),
                      child: RankListTile(entry: entry),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}

