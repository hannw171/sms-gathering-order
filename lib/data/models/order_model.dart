import 'pharmacy_model.dart';

enum OrderStatus { submitted, verified, cancelled }

class OrderItemSummary {
  final String productId;
  final String productName;
  final String uom;
  final int quantity;
  final int unitPrice;

  const OrderItemSummary({
    required this.productId,
    required this.productName,
    required this.uom,
    required this.quantity,
    required this.unitPrice,
  });

  int get subtotal => quantity * unitPrice;

  Map<String, dynamic> toJson() => {
        'product_id': productId,
        'product_name': productName,
        'uom': uom,
        'quantity': quantity,
        'unit_price': unitPrice,
        'subtotal': subtotal,
      };
}

/// Model representing a Submitted Gathering Order
class OrderModel {
  final String id;
  final String orderNumber;
  final PharmacyModel pharmacy;
  final List<OrderItemSummary> items;
  final int totalAmount;
  final String? unlockedRewardTitle;
  final int currentLeaderboardRank;
  final OrderStatus status;
  final DateTime createdAt;

  const OrderModel({
    required this.id,
    required this.orderNumber,
    required this.pharmacy,
    required this.items,
    required this.totalAmount,
    this.unlockedRewardTitle,
    required this.currentLeaderboardRank,
    this.status = OrderStatus.submitted,
    required this.createdAt,
  });
}

