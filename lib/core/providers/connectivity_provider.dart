import 'package:flutter/foundation.dart';
import '../services/network_quality_service.dart';

/// Provider managing ballroom Wi-Fi / network status indicator
class ConnectivityProvider extends ChangeNotifier {
  final NetworkQualityService networkService;

  ConnectionStatus _status = ConnectionStatus.online;
  bool _isChecking = false;

  ConnectivityProvider({required this.networkService});

  ConnectionStatus get status => _status;
  bool get isChecking => _isChecking;
  bool get isOffline => _status == ConnectionStatus.offline;

  Future<void> refreshConnectivity() async {
    if (_isChecking) return;
    _isChecking = true;
    notifyListeners();

    _status = await networkService.checkStatus();
    _isChecking = false;
    notifyListeners();
  }

  void setStatus(ConnectionStatus newStatus) {
    if (_status == newStatus) return;
    _status = newStatus;
    notifyListeners();
  }
}

