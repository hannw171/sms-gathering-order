import 'package:flutter/foundation.dart';
import '../../../data/models/order_model.dart';
import '../../../data/models/pharmacy_model.dart';
import '../../../data/models/product_model.dart';
import '../../../data/models/reward_tier_model.dart';
import '../../../data/services/api_service.dart';
import '../models/cart_item_model.dart';

/// ViewModel / Provider managing Cart Items, Dynamic Reward Tier Calculation & Checkout
class CartProvider extends ChangeNotifier {
  final ApiService apiService;

  final Map<String, CartItemModel> _items = {};
  List<RewardTierModel> _rewardTiers = [];
  bool _isSubmitting = false;
  String? _submitError;

  CartProvider({required this.apiService}) {
    loadRewardTiers();
  }

  List<CartItemModel> get items => _items.values.toList();
  List<RewardTierModel> get rewardTiers => _rewardTiers;
  bool get isSubmitting => _isSubmitting;
  String? get submitError => _submitError;
  bool get isEmpty => _items.isEmpty;

  int get totalUniqueProducts => _items.length;

  int get totalItemsCount =>
      _items.values.fold(0, (sum, item) => sum + item.quantity);

  int get totalAmount =>
      _items.values.fold(0, (sum, item) => sum + item.subtotal);

  int getQuantity(String productId) => _items[productId]?.quantity ?? 0;

  /// Highest unlocked reward tier based on current totalAmount
  RewardTierModel? get currentUnlockedTier {
    if (_rewardTiers.isEmpty) return null;
    RewardTierModel? unlocked;
    for (final tier in _rewardTiers) {
      if (totalAmount >= tier.minSpend) {
        unlocked = tier;
      }
    }
    return unlocked;
  }

  /// Next target reward tier to unlock
  RewardTierModel? get nextTargetTier {
    for (final tier in _rewardTiers) {
      if (totalAmount < tier.minSpend) {
        return tier;
      }
    }
    return null;
  }

  /// Progress (0.0 - 1.0) toward the next reward tier
  double get nextTierProgress {
    final next = nextTargetTier;
    if (next == null) return 1.0;
    if (next.minSpend <= 0) return 1.0;
    return (totalAmount / next.minSpend).clamp(0.0, 1.0);
  }

  /// Remaining Rupiah needed to unlock the next reward tier
  int get remainingToNextTier {
    final next = nextTargetTier;
    if (next == null) return 0;
    return (next.minSpend - totalAmount).clamp(0, next.minSpend);
  }

  Future<void> loadRewardTiers() async {
    _rewardTiers = await apiService.getRewardTiers();
    notifyListeners();
  }

  void setQuantity(ProductModel product, int quantity) {
    if (quantity <= 0) {
      _items.remove(product.id);
    } else {
      _items[product.id] = CartItemModel(product: product, quantity: quantity);
    }
    notifyListeners();
  }

  void increment(ProductModel product) {
    final current = getQuantity(product.id);
    setQuantity(product, current + 1);
  }

  void decrement(ProductModel product) {
    final current = getQuantity(product.id);
    setQuantity(product, current - 1);
  }

  void clearCart() {
    _items.clear();
    _submitError = null;
    notifyListeners();
  }

  Future<OrderModel?> submitOrder(PharmacyModel pharmacy) async {
    if (_items.isEmpty) return null;
    _isSubmitting = true;
    _submitError = null;
    notifyListeners();

    try {
      final orderItems = _items.values
          .map(
            (e) => OrderItemSummary(
              productId: e.product.id,
              productName: e.product.name,
              uom: e.product.uom,
              quantity: e.quantity,
              unitPrice: e.product.unitPrice,
            ),
          )
          .toList();

      final order = await apiService.submitOrder(
        pharmacy: pharmacy,
        items: orderItems,
        totalAmount: totalAmount,
        unlockedRewardTitle: currentUnlockedTier?.rewardDescription,
      );

      clearCart();
      return order;
    } catch (e) {
      _submitError = 'Gagal mengirim pesanan. Periksa koneksi jaringan Anda.';
      return null;
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }
}

