import '../../data/services/api_service.dart';

enum ConnectionStatus { online, slow, offline }

/// Service for checking API reachability and latency in the ballroom / event venue
class NetworkQualityService {
  final ApiService apiService;

  const NetworkQualityService({required this.apiService});

  Future<ConnectionStatus> checkStatus() async {
    final stopwatch = Stopwatch()..start();
    try {
      final isReachable = await apiService.ping();
      stopwatch.stop();
      if (!isReachable) return ConnectionStatus.offline;
      if (stopwatch.elapsedMilliseconds > 1500) {
        return ConnectionStatus.slow;
      }
      return ConnectionStatus.online;
    } catch (_) {
      return ConnectionStatus.offline;
    }
  }
}

