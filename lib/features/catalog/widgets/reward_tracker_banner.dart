import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/widgets/reward_progress_bar.dart';
import '../../cart/providers/cart_provider.dart';

/// Sticky / Top Banner displaying Dynamic Reward Tier Progress in Catalog
/// (Figma Node: 1:420 - Katalog Belanja & Dynamic Reward Tracker)
class RewardTrackerBanner extends StatelessWidget {
  final CartProvider cart;

  const RewardTrackerBanner({
    super.key,
    required this.cart,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;
    final unlocked = cart.currentUnlockedTier;
    final nextTier = cart.nextTargetTier;

    return Container(
      padding: AppMetrics.paddingCard,
      decoration: BoxDecoration(
        color: c.surfaceDefault,
        borderRadius: AppMetrics.roundedXl,
        border: Border.all(color: c.borderDecorative),
        boxShadow: AppMetrics.shadowSm,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: c.rewardAccent.withValues(alpha: 0.2),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  Icons.emoji_events_rounded,
                  size: 18,
                  color: c.statusWarning,
                ),
              ),
              const SizedBox(width: AppMetrics.space2),
              Expanded(
                child: Text(
                  unlocked != null
                      ? 'Tier Aktif: ${unlocked.title}'
                      : 'Progress Reward Gathering',
                  style: AppTypography.labelLarge(color: c.textPrimary),
                ),
              ),
              Text(
                '${(cart.nextTierProgress * 100).toInt()}%',
                style: AppTypography.labelLarge(color: c.actionPrimary),
              ),
            ],
          ),
          const SizedBox(height: AppMetrics.space2),
          RewardProgressBar(progress: cart.nextTierProgress),
          const SizedBox(height: AppMetrics.space2),
          Text(
            nextTier != null
                ? 'Kurang ${CurrencyFormatter.format(cart.remainingToNextTier)} menuju ${nextTier.title} (${nextTier.rewardDescription})'
                : 'Semua Tier Hadiah Telah Terbuka!',
            style: AppTypography.bodySmall(color: c.textSecondary),
          ),
        ],
      ),
    );
  }
}

