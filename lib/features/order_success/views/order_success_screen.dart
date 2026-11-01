import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/utils/date_formatter.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../data/models/order_model.dart';
import '../../leaderboard/views/leaderboard_screen.dart';
import '../widgets/rank_milestone_card.dart';

/// Order Success & Rank Milestone Screen (Figma Node: 1:1330)
class OrderSuccessScreen extends StatelessWidget {
  final OrderModel order;

  const OrderSuccessScreen({
    super.key,
    required this.order,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: AppMetrics.paddingScreen,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const SizedBox(height: AppMetrics.space6),
              Container(
                width: 72,
                height: 72,
                decoration: BoxDecoration(
                  color: c.statusSuccessContainer,
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  Icons.check_circle_rounded,
                  size: 44,
                  color: c.statusSuccess,
                ),
              ),
              const SizedBox(height: AppMetrics.space4),
              Text(
                'Pesanan Berhasil Dikirim!',
                style: AppTypography.headlineSmall(color: c.textPrimary),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: AppMetrics.space1),
              Text(
                'No. Order: ${order.orderNumber} • ${DateFormatter.formatDateTime(order.createdAt)}',
                style: AppTypography.bodySmall(color: c.textSecondary),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: AppMetrics.space5),
              Container(
                width: double.infinity,
                padding: AppMetrics.paddingCard,
                decoration: BoxDecoration(
                  color: c.surfaceDefault,
                  borderRadius: AppMetrics.roundedXl,
                  border: Border.all(color: c.borderDecorative),
                  boxShadow: AppMetrics.shadowSm,
                ),
                child: Column(
                  children: [
                    Text(
                      'TOTAL TRANSAKSI GATHERING',
                      style: AppTypography.labelLarge(color: c.textMuted),
                    ),
                    const SizedBox(height: AppMetrics.space1),
                    Text(
                      CurrencyFormatter.format(order.totalAmount),
                      style: AppTypography.displaySmall(color: c.actionPrimary),
                    ),
                    const SizedBox(height: AppMetrics.space3),
                    const Divider(),
                    const SizedBox(height: AppMetrics.space2),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Apotek Pemesan',
                            style: AppTypography.bodySmall(color: c.textSecondary)),
                        Text(order.pharmacy.name,
                            style: AppTypography.labelLarge(color: c.textPrimary)),
                      ],
                    ),
                    const SizedBox(height: AppMetrics.space2),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Jumlah Item Produk',
                            style: AppTypography.bodySmall(color: c.textSecondary)),
                        Text('${order.items.length} SKU Produk',
                            style: AppTypography.labelLarge(color: c.textPrimary)),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppMetrics.space4),
              RankMilestoneCard(order: order),
              const SizedBox(height: AppMetrics.space6),
              CustomButton(
                label: 'Lihat Papan Peringkat Most Spender',
                icon: Icons.emoji_events_rounded,
                onPressed: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const LeaderboardScreen()),
                  );
                },
              ),
              const SizedBox(height: AppMetrics.space3),
              CustomButton(
                label: 'Tambah Pesanan Baru',
                variant: CustomButtonVariant.outline,
                onPressed: () => Navigator.of(context).pop(),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

