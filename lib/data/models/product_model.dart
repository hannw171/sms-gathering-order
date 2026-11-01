/// Model representing a Pharmaceutical Product and Principal in the Catalog
class ProductModel {
  final String id;
  final String sku;
  final String name;
  final String principalId;
  final String principalName;
  final String uom; // e.g., "Box @ 10 Strip"
  final int unitPrice;
  final int originalPrice;
  final String? promoBadge; // e.g., "Diskon 15% + Bonus 1 Box"
  final String? imageUrl;
  final int stockAvailable;

  const ProductModel({
    required this.id,
    required this.sku,
    required this.name,
    required this.principalId,
    required this.principalName,
    required this.uom,
    required this.unitPrice,
    required this.originalPrice,
    this.promoBadge,
    this.imageUrl,
    this.stockAvailable = 999,
  });

  bool get hasDiscount => originalPrice > unitPrice;

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    return ProductModel(
      id: json['id']?.toString() ?? '',
      sku: json['sku']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      principalId: json['principal_id']?.toString() ?? '',
      principalName: json['principal_name']?.toString() ?? '',
      uom: json['uom']?.toString() ?? 'Box',
      unitPrice: (json['unit_price'] as num?)?.toInt() ?? 0,
      originalPrice: (json['original_price'] as num?)?.toInt() ?? 0,
      promoBadge: json['promo_badge']?.toString(),
      imageUrl: json['image_url']?.toString(),
      stockAvailable: (json['stock_available'] as num?)?.toInt() ?? 999,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'sku': sku,
        'name': name,
        'principal_id': principalId,
        'principal_name': principalName,
        'uom': uom,
        'unit_price': unitPrice,
        'original_price': originalPrice,
        'promo_badge': promoBadge,
        'image_url': imageUrl,
        'stock_available': stockAvailable,
      };
}

