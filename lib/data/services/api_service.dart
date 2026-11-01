import 'package:dio/dio.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/app_config.dart';
import '../models/leaderboard_entry_model.dart';
import '../models/order_model.dart';
import '../models/pharmacy_model.dart';
import '../models/product_model.dart';
import '../models/reward_tier_model.dart';

/// Centralized REST API Service with realistic Mock Data fallback for SMS Gathering
class ApiService {
  late final Dio _dio;
  final bool useMock;

  ApiService({Dio? dio, this.useMock = AppConfig.useMockFallback}) {
    _dio = dio ??
        Dio(
          BaseOptions(
            baseUrl: AppConfig.apiBaseUrl,
            connectTimeout: AppConfig.connectTimeout,
            receiveTimeout: AppConfig.receiveTimeout,
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
          ),
        );
  }

  Future<bool> ping() async {
    if (useMock) return true;
    try {
      final response = await _dio.get(ApiEndpoints.stageStatus);
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  /// Fetch registered guest pharmacies for onboarding search
  Future<List<PharmacyModel>> getPharmacies({String? query}) async {
    if (!useMock) {
      try {
        final response = await _dio.get(
          ApiEndpoints.pharmacies,
          queryParameters: query != null ? {'q': query} : null,
        );
        final list = response.data['data'] as List<dynamic>? ?? [];
        return list
            .map((item) => PharmacyModel.fromJson(item as Map<String, dynamic>))
            .toList();
      } catch (_) {
        // Fallback to mock on error if needed
      }
    }

    await Future.delayed(const Duration(milliseconds: 250));
    final all = _seedPharmacies;
    if (query == null || query.trim().isEmpty) return all;
    final q = query.trim().toLowerCase();
    return all
        .where((p) =>
            p.name.toLowerCase().contains(q) ||
            p.code.toLowerCase().contains(q) ||
            p.city.toLowerCase().contains(q) ||
            p.ownerName.toLowerCase().contains(q))
        .toList();
  }

  /// Fetch catalog products & promo items
  Future<List<ProductModel>> getProducts() async {
    if (!useMock) {
      try {
        final response = await _dio.get(ApiEndpoints.products);
        final list = response.data['data'] as List<dynamic>? ?? [];
        return list
            .map((item) => ProductModel.fromJson(item as Map<String, dynamic>))
            .toList();
      } catch (_) {}
    }

    await Future.delayed(const Duration(milliseconds: 300));
    return _seedProducts;
  }

  /// Fetch gamification reward tiers
  Future<List<RewardTierModel>> getRewardTiers() async {
    if (!useMock) {
      try {
        final response = await _dio.get(ApiEndpoints.rewardTiers);
        final list = response.data['data'] as List<dynamic>? ?? [];
        return list
            .map((item) =>
                RewardTierModel.fromJson(item as Map<String, dynamic>))
            .toList();
      } catch (_) {}
    }

    return _seedRewardTiers;
  }

  /// Submit gathering order
  Future<OrderModel> submitOrder({
    required PharmacyModel pharmacy,
    required List<OrderItemSummary> items,
    required int totalAmount,
    String? unlockedRewardTitle,
  }) async {
    await Future.delayed(const Duration(milliseconds: 600));
    final timestamp = DateTime.now();
    final orderNo =
        'GTH-${timestamp.hour.toString().padLeft(2, '0')}${timestamp.minute.toString().padLeft(2, '0')}-${timestamp.millisecond.toString().padLeft(3, '0')}';

    // Calculate simulated rank on leaderboard based on spend
    int rank = 12;
    if (totalAmount >= 150000000) {
      rank = 1;
    } else if (totalAmount >= 100000000) {
      rank = 2;
    } else if (totalAmount >= 75000000) {
      rank = 3;
    } else if (totalAmount >= 50000000) {
      rank = 5;
    } else if (totalAmount >= 25000000) {
      rank = 8;
    }

    return OrderModel(
      id: 'ord_${timestamp.millisecondsSinceEpoch}',
      orderNumber: orderNo,
      pharmacy: pharmacy,
      items: items,
      totalAmount: totalAmount,
      unlockedRewardTitle: unlockedRewardTitle,
      currentLeaderboardRank: rank,
      status: OrderStatus.submitted,
      createdAt: timestamp,
    );
  }

  /// Fetch Live Leaderboard entries
  Future<List<LeaderboardEntryModel>> getLeaderboard() async {
    if (!useMock) {
      try {
        final response = await _dio.get(ApiEndpoints.leaderboard);
        final list = response.data['data'] as List<dynamic>? ?? [];
        return list
            .map((item) =>
                LeaderboardEntryModel.fromJson(item as Map<String, dynamic>))
            .toList();
      } catch (_) {}
    }

    await Future.delayed(const Duration(milliseconds: 250));
    return _seedLeaderboard;
  }

  static const List<PharmacyModel> _seedPharmacies = [
    PharmacyModel(
      id: 'ph_01',
      code: 'APT-SBY-001',
      name: 'Apotek Sehat Jaya Makmur',
      ownerName: 'apt. Budi Santoso, S.Farm.',
      city: 'Surabaya',
      address: 'Jl. Dharmawangsa No. 88, Gubeng, Surabaya',
      tableNumber: 'VIP-01',
    ),
    PharmacyModel(
      id: 'ph_02',
      code: 'APT-MLG-014',
      name: 'Apotek Kimia Husada Utama',
      ownerName: 'dr. Hendra Wijaya',
      city: 'Malang',
      address: 'Jl. Ijen Besar No. 42, Klojen, Malang',
      tableNumber: 'VIP-02',
    ),
    PharmacyModel(
      id: 'ph_03',
      code: 'APT-SDA-009',
      name: 'Apotek Berkah Medika Farma',
      ownerName: 'apt. Siti Aminah, S.Si.',
      city: 'Sidoarjo',
      address: 'Jl. Pahlawan No. 15, Sidoarjo',
      tableNumber: 'REG-05',
      hasActiveDeviceSession: true,
    ),
    PharmacyModel(
      id: 'ph_04',
      code: 'APT-GRS-022',
      name: 'Apotek Graha Farma Sentosa',
      ownerName: 'H. Ahmad Fauzi',
      city: 'Gresik',
      address: 'Jl. Veteran No. 110, Kebomas, Gresik',
      tableNumber: 'REG-08',
    ),
  ];

  static const List<RewardTierModel> _seedRewardTiers = [
    RewardTierModel(
      id: 'tier_1',
      tierLevel: 1,
      title: 'Silver Tier',
      rewardDescription: 'Voucher Diskon Tambahan Rp 500.000',
      minSpend: 10000000,
    ),
    RewardTierModel(
      id: 'tier_2',
      tierLevel: 2,
      title: 'Gold Tier',
      rewardDescription: 'Logam Mulia Antam 2 Gram',
      minSpend: 25000000,
    ),
    RewardTierModel(
      id: 'tier_3',
      tierLevel: 3,
      title: 'Platinum Tier',
      rewardDescription: 'Logam Mulia Antam 5 Gram + Cashback 2%',
      minSpend: 50000000,
    ),
    RewardTierModel(
      id: 'tier_4',
      tierLevel: 4,
      title: 'Diamond Crown Tier',
      rewardDescription: 'iPhone 16 Pro / Paket Tour Jepang + Cashback 3%',
      minSpend: 100000000,
    ),
  ];

  static const List<ProductModel> _seedProducts = [
    ProductModel(
      id: 'prd_01',
      sku: 'SNF-AMX-500',
      name: 'Amoxicillin Trihydrate 500mg Forte',
      principalId: 'prc_sanbe',
      principalName: 'Sanbe Farma',
      uom: 'Box @ 10 Strip × 10 Kaplet',
      unitPrice: 48500,
      originalPrice: 56000,
      promoBadge: 'Diskon Gathering 13% + Bonus 1 Box per 10 Box',
    ),
    ProductModel(
      id: 'prd_02',
      sku: 'SNF-CEF-100',
      name: 'Cefixime 100mg Kapsul Generik Bermerk',
      principalId: 'prc_sanbe',
      principalName: 'Sanbe Farma',
      uom: 'Box @ 5 Strip × 10 Kapsul',
      unitPrice: 112000,
      originalPrice: 130000,
      promoBadge: 'Diskon 14%',
    ),
    ProductModel(
      id: 'prd_03',
      sku: 'KLB-NRB-5000',
      name: 'Neurobion Forte Pink Vitamin B1 B6 B12',
      principalId: 'prc_kalbe',
      principalName: 'Kalbe Farma',
      uom: 'Box @ 25 Strip × 10 Tablet',
      unitPrice: 495000,
      originalPrice: 540000,
      promoBadge: 'Paket Bundling Spesial Gathering',
    ),
    ProductModel(
      id: 'prd_04',
      sku: 'KLB-PRG-650',
      name: 'Paracetamol 650mg Kaplet Analgesik',
      principalId: 'prc_kalbe',
      principalName: 'Kalbe Farma',
      uom: 'Karton @ 20 Box',
      unitPrice: 640000,
      originalPrice: 720000,
      promoBadge: 'Hemat Rp 80.000 / Karton',
    ),
    ProductModel(
      id: 'prd_05',
      sku: 'DXM-OMZ-20',
      name: 'Omeprazole 20mg Kapsul Gastro',
      principalId: 'prc_dexa',
      principalName: 'Dexa Medica',
      uom: 'Box @ 3 Strip × 10 Kapsul',
      unitPrice: 36000,
      originalPrice: 42000,
      promoBadge: 'Diskon 15%',
    ),
    ProductModel(
      id: 'prd_06',
      sku: 'DXM-CTZ-10',
      name: 'Cetirizine HCl 10mg Antihistamin',
      principalId: 'prc_dexa',
      principalName: 'Dexa Medica',
      uom: 'Karton @ 50 Box',
      unitPrice: 950000,
      originalPrice: 1100000,
      promoBadge: 'Best Seller Gathering',
    ),
    ProductModel(
      id: 'prd_07',
      sku: 'PHR-VITC-1000',
      name: 'Enervon-C Active Multivitamin Zinc',
      principalId: 'prc_pharos',
      principalName: 'Pharos Indonesia',
      uom: 'Box @ 25 Strip × 4 Tablet',
      unitPrice: 138000,
      originalPrice: 155000,
      promoBadge: 'Bonus 2 Strip',
    ),
  ];

  static const List<LeaderboardEntryModel> _seedLeaderboard = [
    LeaderboardEntryModel(
      rank: 1,
      previousRank: 1,
      pharmacyId: 'ph_01',
      pharmacyName: 'Apotek Sehat Jaya Makmur',
      city: 'Surabaya',
      totalSpend: 148500000,
      totalOrders: 4,
      unlockedTierBadge: 'Diamond Crown Tier',
    ),
    LeaderboardEntryModel(
      rank: 2,
      previousRank: 3,
      pharmacyId: 'ph_02',
      pharmacyName: 'Apotek Kimia Husada Utama',
      city: 'Malang',
      totalSpend: 112400000,
      totalOrders: 3,
      unlockedTierBadge: 'Diamond Crown Tier',
    ),
    LeaderboardEntryModel(
      rank: 3,
      previousRank: 2,
      pharmacyId: 'ph_04',
      pharmacyName: 'Apotek Graha Farma Sentosa',
      city: 'Gresik',
      totalSpend: 86750000,
      totalOrders: 3,
      unlockedTierBadge: 'Platinum Tier',
    ),
    LeaderboardEntryModel(
      rank: 4,
      previousRank: 4,
      pharmacyId: 'ph_03',
      pharmacyName: 'Apotek Berkah Medika Farma',
      city: 'Sidoarjo',
      totalSpend: 64200000,
      totalOrders: 2,
      unlockedTierBadge: 'Platinum Tier',
    ),
    LeaderboardEntryModel(
      rank: 5,
      previousRank: 6,
      pharmacyId: 'ph_05',
      pharmacyName: 'Apotek Nusantara Sehat',
      city: 'Mojokerto',
      totalSpend: 42800000,
      totalOrders: 2,
      unlockedTierBadge: 'Gold Tier',
    ),
    LeaderboardEntryModel(
      rank: 6,
      previousRank: 5,
      pharmacyId: 'ph_06',
      pharmacyName: 'Apotek Mitra Keluarga Farma',
      city: 'Pasuruan',
      totalSpend: 31500000,
      totalOrders: 1,
      unlockedTierBadge: 'Gold Tier',
    ),
  ];
}

