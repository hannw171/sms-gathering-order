import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../models/cart_item_model.dart';

/// Tile displaying a single item in the Shopping Cart with quantity stepper
class CartItemTile extends StatelessWidget {
  final CartItemModel item;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;

  const CartItemTile({
    super.key,
    required this.item,
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
        borderRadius: AppMetrics.roundedLg,
        border: Border.all(color: c.borderDecorative),
        boxShadow: AppMetrics.shadowSm,
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.product.principalName.toUpperCase(),
                  style: AppTypography.caption(color: c.actionPrimary),
                ),
                const SizedBox(height: 2),
                Text(
                  item.product.name,
                  style: AppTypography.titleMedium(color: c.textPrimary),
                ),
                const SizedBox(height: 2),
                Text(
                  '${item.product.uom} • ${CurrencyFormatter.format(item.product.unitPrice)}',
                  style: AppTypography.bodySmall(color: c.textSecondary),
                ),
                const SizedBox(height: AppMetrics.space2),
                Text(
                  'Subtotal: ${CurrencyFormatter.format(item.subtotal)}',
                  style: AppTypography.priceMedium(color: c.actionPrimary),
                ),
              ],
            ),
          ),
          const SizedBox(width: AppMetrics.space3),
          Container(
            decoration: BoxDecoration(
              border: Border.all(color: c.borderDecorative),
              borderRadius: AppMetrics.roundedMd,
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                IconButton(
                  constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
                  padding: EdgeInsets.zero,
                  icon: Icon(
                    item.quantity == 1
                        ? Icons.delete_outline_rounded
                        : Icons.remove_rounded,
                    size: 18,
                    color: item.quantity == 1 ? c.statusError : c.textPrimary,
                  ),
                  onPressed: onDecrement,
                ),
                SizedBox(
                  width: 32,
                  child: Text(
                    '${item.quantity}',
                    textAlign: TextAlign.center,
                    style: AppTypography.labelLarge(color: c.textPrimary),
                  ),
                ),
                IconButton(
                  constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
                  padding: EdgeInsets.zero,
                  icon: Icon(
                    Icons.add_rounded,
                    size: 18,
                    color: c.actionPrimary,
                  ),
                  onPressed: onIncrement,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

