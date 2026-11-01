import 'package:flutter/foundation.dart';
import '../../../data/models/pharmacy_model.dart';
import '../../../data/services/api_service.dart';

/// ViewModel / Provider for Guest Pharmacy Identification & Onboarding
class OnboardingProvider extends ChangeNotifier {
  final ApiService apiService;

  List<PharmacyModel> _pharmacies = [];
  PharmacyModel? _selectedPharmacy;
  bool _isLoading = false;
  String _searchQuery = '';
  String? _errorMessage;

  OnboardingProvider({required this.apiService}) {
    loadPharmacies();
  }

  List<PharmacyModel> get pharmacies => _pharmacies;
  PharmacyModel? get selectedPharmacy => _selectedPharmacy;
  bool get isLoading => _isLoading;
  String get searchQuery => _searchQuery;
  String? get errorMessage => _errorMessage;

  Future<void> loadPharmacies({String? query}) async {
    _isLoading = true;
    _errorMessage = null;
    if (query != null) _searchQuery = query;
    notifyListeners();

    try {
      _pharmacies = await apiService.getPharmacies(query: _searchQuery);
    } catch (e) {
      _errorMessage = 'Gagal memuat daftar apotek tamu.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void selectPharmacy(PharmacyModel pharmacy) {
    _selectedPharmacy = pharmacy;
    notifyListeners();
  }

  void clearSelection() {
    _selectedPharmacy = null;
    notifyListeners();
  }
}

