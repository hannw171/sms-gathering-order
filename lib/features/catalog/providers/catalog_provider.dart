import 'package:flutter/foundation.dart';
import '../../../data/models/product_model.dart';
import '../../../data/services/api_service.dart';

/// ViewModel / Provider for Catalog Products, Principal Filtering & Search
class CatalogProvider extends ChangeNotifier {
  final ApiService apiService;

  List<ProductModel> _allProducts = [];
  String _selectedPrincipalId = 'all';
  String _searchQuery = '';
  bool _isLoading = false;
  String? _errorMessage;

  CatalogProvider({required this.apiService}) {
    loadCatalog();
  }

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String get selectedPrincipalId => _selectedPrincipalId;
  String get searchQuery => _searchQuery;

  /// List of distinct Principals available in the catalog
  List<Map<String, String>> get principals {
    final seen = <String, String>{'all': 'Semua Pabrik'};
    for (final p in _allProducts) {
      seen[p.principalId] = p.principalName;
    }
    return seen.entries
        .map((e) => {'id': e.key, 'name': e.value})
        .toList();
  }

  /// Filtered products based on selected principal tab and search query
  List<ProductModel> get filteredProducts {
    return _allProducts.where((p) {
      final matchesPrincipal =
          _selectedPrincipalId == 'all' || p.principalId == _selectedPrincipalId;
      final q = _searchQuery.trim().toLowerCase();
      final matchesSearch = q.isEmpty ||
          p.name.toLowerCase().contains(q) ||
          p.sku.toLowerCase().contains(q) ||
          p.principalName.toLowerCase().contains(q);
      return matchesPrincipal && matchesSearch;
    }).toList();
  }

  Future<void> loadCatalog() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _allProducts = await apiService.getProducts();
    } catch (e) {
      _errorMessage = 'Gagal memuat katalog produk gathering.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void selectPrincipal(String principalId) {
    if (_selectedPrincipalId == principalId) return;
    _selectedPrincipalId = principalId;
    notifyListeners();
  }

  void setSearchQuery(String query) {
    _searchQuery = query;
    notifyListeners();
  }
}

