import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/theme.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../data/models/pharmacy_model.dart';
import '../../order_success/views/order_success_screen.dart';
import '../providers/cart_provider.dart';
import '../widgets/cart_item_tile.dart';
import '../widgets/reward_review_card.dart';

/// Shopping Cart & Reward Review Screen (Figma Node: 1:735, 1:624, 1:1061)
class CartScreen extends StatelessWidget {
  final PharmacyModel pharmacy;

  const CartScreen({
    super.key,
    required this.pharmacy,
  });

  Future<void> _submitOrder(BuildContext context, CartProvider cart) async {
    final order = await cart.submitOrder(pharmacy);
    if (!context.mounted) return;
    if (order != null) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => OrderSuccessScreen(order: order),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = context.colors;
    final cart = context.watch<CartProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Keranjang Belanja (${cart.totalUniqueProducts})',
          style: AppTypography.titleLarge(color: c.textPrimary),
        ),
        actions: [
          if (!cart.isEmpty)
            TextButton(
              onPressed: cart.clearCart,
              child: Text(
                'Kosongkan',
                style: AppTypography.labelLarge(color: c.statusError),
              ),
            ),
        ],
      ),
      body: cart.isEmpty
          ? _buildEmptyState(context)
          : Column(
              children: [
                if (cart.submitError != null)
                  Container(
                    width: double.infinity,
                    margin: const EdgeInsets.fromLTRB(
                      AppMetrics.space4,
                      AppMetrics.space3,
                      AppMetrics.space4,
                      0,
                    ),
                    padding: AppMetrics.paddingCardSm,
                    decoration: BoxDecoration(
                      color: c.statusErrorContainer,
                      borderRadius: AppMetrics.roundedMd,
                      border: Border.all(color: c.statusError),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.error_outline_rounded, color: c.statusError),
                        const SizedBox(width: AppMetrics.space2),
                        Expanded(
                          child: Text(
                            cart.submitError!,
                            style: AppTypography.bodySmall(
                                color: c.statusOnErrorContainer),
                          ),
                        ),
                      ],
                    ),
                  ),
                Expanded(
                  child: ListView(
                    padding: AppMetrics.paddingScreen,
                    children: [
                      RewardReviewCard(cart: cart),
                      const SizedBox(height: AppMetrics.space4),
                      ...cart.items.map(
                        (item) => Padding(
                          padding:
                              const EdgeInsets.only(bottom: AppMetrics.space3),
                          child: CartItemTile(
                            item: item,
                            onIncrement: () => cart.increment(item.product),
                            onDecrement: () => cart.decrement(item.product),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: AppMetrics.paddingCard,
                  decoration: BoxDecoration(
                    color: c.surfaceDefault,
                    boxShadow: AppMetrics.shadowDrawer,
                    border: Border(top: BorderSide(color: c.borderDecorative)),
                  ),
                  child: SafeArea(
                    top: false,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Total Estimasi Pesanan',
                              style: AppTypography.bodyMedium(
                                  color: c.textSecondary),
                            ),
                            Text(
                              CurrencyFormatter.format(cart.totalAmount),
                              style: AppTypography.priceLarge(
                                  color: c.actionPrimary),
                            ),
                          ],
                        ),
                        const SizedBox(height: AppMetrics.space3),
                        CustomButton(
                          label: 'Kirim Pesanan Gathering',
                          icon: Icons.send_rounded,
                          isLoading: cart.isSubmitting,
                          onPressed: () => _submitOrder(context, cart),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    final c = context.colors;
    return Center(
      child: Padding(
        padding: AppMetrics.paddingScreen,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.shopping_bag_outlined, size: 64, color: c.textMuted),
            const SizedBox(height: AppMetrics.space4),
            Text(
              'Keranjang Belanja Kosong',
              style: AppTypography.titleLarge(color: c.textPrimary),
            ),
            const SizedBox(height: AppMetrics.space2),
            Text(
              'Pilih produk obat promo dari katalog untuk mulai mengumpulkan akumulasi transaksi dan hadiah langsung.',
              style: AppTypography.bodyMedium(color: c.textSecondary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppMetrics.space5),
            CustomButton(
              label: 'Kembali ke Katalog',
              isFullWidth: false,
              onPressed: () => Navigator.of(context).pop(),
            ),
          ],
        ),
      ),
    );
  }
}
