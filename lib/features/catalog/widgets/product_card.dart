import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../data/models/product_model.dart';

/// Product Catalog Card with Quantity Stepper (DESIGN.md Section 6.2 & Figma 1:420)
class ProductCard extends StatelessWidget {
  final ProductModel product;
  final int quantity;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;

  const ProductCard({
    super.key,
    required this.product,
    required this.quantity,
    required this.onIncrement,
    required this.onDecrement,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    return Container(
      padding: AppMetrics.paddingCard,
      decoration: BoxDecoration(
        color: c.surfaceDefault,
        borderRadius: AppMetrics.roundedXl,
        border: Border.all(
          color: quantity > 0 ? c.actionPrimary : c.borderDecorative,
          width: quantity > 0 ? 1.5 : 1.0,
        ),
        boxShadow: AppMetrics.shadowSm,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1:1 Product Thumbnail Container
              Container(
                width: 72,
                height: 72,
                decoration: BoxDecoration(
                  color: c.surfaceSubtle,
                  borderRadius: AppMetrics.roundedMd,
                  border: Border.all(color: c.borderDecorative),
                ),
                child: Icon(
                  Icons.medication_rounded,
                  size: 36,
                  color: c.actionPrimary,
                ),
              ),
              const SizedBox(width: AppMetrics.space3),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${product.principalName} • ${product.sku}',
                      style: AppTypography.caption(color: c.textMuted),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      product.name,
                      style: AppTypography.titleMedium(color: c.textPrimary),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: c.surfaceSubtle,
                        borderRadius: AppMetrics.roundedXs,
                        border: Border.all(color: c.borderDecorative),
                      ),
                      child: Text(
                        product.uom,
                        style: AppTypography.bodySmall(color: c.textSecondary),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if (product.promoBadge != null) ...[
            const SizedBox(height: AppMetrics.space3),
            StatusBadge(
              label: product.promoBadge!,
              type: StatusBadgeType.success,
              icon: Icons.local_offer_rounded,
            ),
          ],
          const SizedBox(height: AppMetrics.space3),
          const Divider(),
          const SizedBox(height: AppMetrics.space2),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (product.hasDiscount)
                    Text(
                      CurrencyFormatter.format(product.originalPrice),
                      style: AppTypography.bodySmall(color: c.textMuted).copyWith(
                        decoration: TextDecoration.lineThrough,
                      ),
                    ),
                  Text(
                    CurrencyFormatter.format(product.unitPrice),
                    style: AppTypography.priceMedium(color: c.actionPrimary),
                  ),
                ],
              ),
              _buildStepper(context),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStepper(BuildContext context) {
    final c = context.colors;
    if (quantity == 0) {
      return FilledButton.icon(
        onPressed: onIncrement,
        style: FilledButton.styleFrom(
          minimumSize: const Size(110, 40),
          padding: const EdgeInsets.symmetric(horizontal: AppMetrics.space3),
        ),
        icon: const Icon(Icons.add_rounded, size: 18),
        label: const Text('Tambah'),
      );
    }

    return Container(
      decoration: BoxDecoration(
        color: c.surfaceSubtle,
        borderRadius: AppMetrics.roundedMd,
        border: Border.all(color: c.actionPrimary),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          IconButton(
            constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
            padding: EdgeInsets.zero,
            onPressed: onDecrement,
            icon: Icon(Icons.remove_rounded, size: 18, color: c.actionPrimary),
          ),
          SizedBox(
            width: 36,
            child: Text(
              '$quantity',
              textAlign: TextAlign.center,
              style: AppTypography.titleMedium(color: c.textPrimary),
            ),
          ),
          IconButton(
            constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
            padding: EdgeInsets.zero,
            onPressed: onIncrement,
            icon: Icon(Icons.add_rounded, size: 18, color: c.actionPrimary),
          ),
        ],
      ),
    );
  }
}

