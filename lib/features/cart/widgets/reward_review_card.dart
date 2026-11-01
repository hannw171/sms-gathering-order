import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/widgets/reward_progress_bar.dart';
import '../providers/cart_provider.dart';

/// Card reviewing the current reward status inside the Cart Screen (Figma Node: 1:735)
class RewardReviewCard extends StatelessWidget {
  final CartProvider cart;

  const RewardReviewCard({
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
        color: unlocked != null ? c.statusSuccessContainer : c.surfaceSubtle,
        borderRadius: AppMetrics.roundedXl,
        border: Border.all(
          color: unlocked != null ? c.statusSuccess : c.borderDecorative,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                unlocked != null
                    ? Icons.verified_rounded
                    : Icons.card_giftcard_rounded,
                color: unlocked != null ? c.statusSuccess : c.actionPrimary,
                size: 20,
              ),
              const SizedBox(width: AppMetrics.space2),
              Expanded(
                child: Text(
                  unlocked != null
                      ? 'Terbuka: ${unlocked.title} (${unlocked.rewardDescription})'
                      : 'Belum Membuka Tier Hadiah',
                  style: AppTypography.labelLarge(
                    color: unlocked != null
                        ? c.statusOnSuccessContainer
                        : c.textPrimary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppMetrics.space3),
          RewardProgressBar(
            progress: cart.nextTierProgress,
            height: 12.0,
          ),
          if (nextTier != null) ...[
            const SizedBox(height: AppMetrics.space2),
            Text(
              'Tambah ${CurrencyFormatter.format(cart.remainingToNextTier)} lagi untuk membuka ${nextTier.title} (${nextTier.rewardDescription})!',
              style: AppTypography.bodySmall(color: c.textSecondary),
            ),
          ] else ...[
            const SizedBox(height: AppMetrics.space2),
            Text(
              'Selamat! Anda telah mencapai Tier Reward Tertinggi!',
              style: AppTypography.bodySmall(color: c.statusSuccess),
            ),
          ],
        ],
      ),
    );
  }
}

