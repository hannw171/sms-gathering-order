import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';

/// Floating Bottom Checkout Pill (DESIGN.md Section 6.3)
/// Dark Slate Luxury (#0F172A) with high-contrast total & item badge
class FloatingCartPill extends StatelessWidget {
  final int totalItems;
  final int totalAmount;
  final VoidCallback onTap;

  const FloatingCartPill({
    super.key,
    required this.totalItems,
    required this.totalAmount,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(
          AppMetrics.space4,
          0,
          AppMetrics.space4,
          AppMetrics.space4,
        ),
        child: Material(
          color: const Color(0xFF0F172A).withValues(alpha: 0.94),
          borderRadius: AppMetrics.roundedFull,
          elevation: 8,
          child: InkWell(
            onTap: onTap,
            borderRadius: AppMetrics.roundedFull,
            child: Container(
              height: AppMetrics.floatingCartHeight,
              padding: const EdgeInsets.symmetric(horizontal: AppMetrics.space5),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: c.rewardAccent,
                      borderRadius: AppMetrics.roundedFull,
                    ),
                    child: Text(
                      '$totalItems Qty',
                      style: AppTypography.labelLarge(color: c.rewardOnAccent),
                    ),
                  ),
                  const SizedBox(width: AppMetrics.space3),
                  Expanded(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Total Keranjang',
                          style: AppTypography.caption(
                            color: Colors.white.withValues(alpha: 0.75),
                          ),
                        ),
                        Text(
                          CurrencyFormatter.format(totalAmount),
                          style: AppTypography.priceLarge(color: Colors.white),
                        ),
                      ],
                    ),
                  ),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Review Order',
                        style: AppTypography.labelLarge(color: Colors.white),
                      ),
                      const SizedBox(width: AppMetrics.space1),
                      const Icon(
                        Icons.arrow_forward_rounded,
                        color: Colors.white,
                        size: 18,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

