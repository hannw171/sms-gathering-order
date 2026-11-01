import 'package:flutter/foundation.dart';
import '../../../data/models/leaderboard_entry_model.dart';
import '../../../data/services/api_service.dart';

/// ViewModel / Provider managing Most Spender Leaderboard & Live Stage Rankings
class LeaderboardProvider extends ChangeNotifier {
  final ApiService apiService;

  List<LeaderboardEntryModel> _entries = [];
  bool _isLoading = false;
  DateTime _lastUpdated = DateTime.now();

  LeaderboardProvider({required this.apiService}) {
    refreshLeaderboard();
  }

  List<LeaderboardEntryModel> get entries => _entries;
  bool get isLoading => _isLoading;
  DateTime get lastUpdated => _lastUpdated;

  List<LeaderboardEntryModel> get topThree =>
      _entries.where((e) => e.rank >= 1 && e.rank <= 3).toList();

  List<LeaderboardEntryModel> get runnerUps =>
      _entries.where((e) => e.rank > 3).toList();

  Future<void> refreshLeaderboard() async {
    _isLoading = true;
    notifyListeners();

    try {
      _entries = await apiService.getLeaderboard();
      _lastUpdated = DateTime.now();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}

