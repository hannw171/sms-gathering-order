import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/providers/connectivity_provider.dart';
import '../../../core/theme/theme.dart';
import '../../../data/models/pharmacy_model.dart';
import '../../cart/providers/cart_provider.dart';
import '../../cart/views/cart_screen.dart';
import '../../leaderboard/views/leaderboard_screen.dart';
import '../providers/catalog_provider.dart';
import '../widgets/floating_cart_pill.dart';
import '../widgets/product_card.dart';
import '../widgets/reward_tracker_banner.dart';

/// Main Shopping Catalog & Dynamic Reward Tracker Screen
/// (Figma Node: 1:420, 1:199, 1:1186)
class CatalogScreen extends StatelessWidget {
  final PharmacyModel pharmacy;

  const CatalogScreen({
    super.key,
    required this.pharmacy,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;
    final catalog = context.watch<CatalogProvider>();
    final cart = context.watch<CartProvider>();
    final connectivity = context.watch<ConnectivityProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              pharmacy.name,
              style: AppTypography.titleMedium(color: c.textPrimary),
            ),
            Text(
              'Meja ${pharmacy.tableNumber} • ${pharmacy.city}',
              style: AppTypography.bodySmall(color: c.textSecondary),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Papan Peringkat',
            icon: Icon(Icons.leaderboard_rounded, color: c.actionPrimary),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const LeaderboardScreen()),
              );
            },
          ),
        ],
      ),
      body: Stack(
        children: [
          Column(
            children: [
              if (connectivity.isOffline)
                Container(
                  width: double.infinity,
                  color: c.statusErrorContainer,
                  padding: const EdgeInsets.symmetric(
                    horizontal: AppMetrics.space4,
                    vertical: AppMetrics.space2,
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.wifi_off_rounded,
                          size: 16, color: c.statusError),
                      const SizedBox(width: AppMetrics.space2),
                      Expanded(
                        child: Text(
                          'Koneksi terputus. Keranjang Anda tetap tersimpan di perangkat.',
                          style: AppTypography.bodySmall(
                              color: c.statusOnErrorContainer),
                        ),
                      ),
                    ],
                  ),
                ),
              Expanded(
                child: ListView(
                  padding: EdgeInsets.fromLTRB(
                    AppMetrics.space4,
                    AppMetrics.space4,
                    AppMetrics.space4,
                    cart.isEmpty ? AppMetrics.space6 : 96.0,
                  ),
                  children: [
                    RewardTrackerBanner(cart: cart),
                    const SizedBox(height: AppMetrics.space4),
                    TextField(
                      onChanged: catalog.setSearchQuery,
                      decoration: InputDecoration(
                        hintText: 'Cari nama obat, zat aktif, atau pabrik...',
                        prefixIcon:
                            Icon(Icons.search_rounded, color: c.textMuted),
                      ),
                    ),
                    const SizedBox(height: AppMetrics.space3),
                    // Horizontal Principal Pill Tabs
                    SizedBox(
                      height: 38,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemCount: catalog.principals.length,
                        separatorBuilder: (_, _) =>
                            const SizedBox(width: AppMetrics.space2),
                        itemBuilder: (context, index) {
                          final principal = catalog.principals[index];
                          final isSelected =
                              catalog.selectedPrincipalId == principal['id'];
                          return ChoiceChip(
                            label: Text(principal['name']!),
                            selected: isSelected,
                            onSelected: (_) =>
                                catalog.selectPrincipal(principal['id']!),
                            selectedColor: c.actionPrimary,
                            backgroundColor: c.surfaceDefault,
                            labelStyle: AppTypography.labelLarge(
                              color: isSelected
                                  ? c.actionOnPrimary
                                  : c.textSecondary,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: AppMetrics.roundedLg,
                              side: BorderSide(
                                color: isSelected
                                    ? c.actionPrimary
                                    : c.borderDecorative,
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                    const SizedBox(height: AppMetrics.space4),
                    if (catalog.isLoading)
                      const Padding(
                        padding: EdgeInsets.all(AppMetrics.space8),
                        child: Center(child: CircularProgressIndicator()),
                      )
                    else if (catalog.filteredProducts.isEmpty)
                      _buildEmptySearch(context)
                    else
                      ...catalog.filteredProducts.map(
                        (product) => Padding(
                          padding:
                              const EdgeInsets.only(bottom: AppMetrics.space3),
                          child: ProductCard(
                            product: product,
                            quantity: cart.getQuantity(product.id),
                            onIncrement: () => cart.increment(product),
                            onDecrement: () => cart.decrement(product),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ],
          ),
          if (!cart.isEmpty)
            Align(
              alignment: Alignment.bottomCenter,
              child: FloatingCartPill(
                totalItems: cart.totalItemsCount,
                totalAmount: cart.totalAmount,
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => CartScreen(pharmacy: pharmacy),
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildEmptySearch(BuildContext context) {
    final c = context.colors;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: AppMetrics.space8),
      child: Column(
        children: [
          Icon(Icons.search_off_rounded, size: 52, color: c.textMuted),
          const SizedBox(height: AppMetrics.space3),
          Text(
            'Produk Tidak Ditemukan',
            style: AppTypography.titleMedium(color: c.textPrimary),
          ),
          const SizedBox(height: AppMetrics.space1),
          Text(
            'Coba gunakan kata kunci lain atau pilih tab "Semua Pabrik".',
            style: AppTypography.bodyMedium(color: c.textSecondary),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

