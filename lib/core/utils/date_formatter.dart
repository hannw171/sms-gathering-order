import 'package:intl/date_symbol_data_local.dart';
import 'package:intl/intl.dart';

/// Indonesian date and time formatting utility
abstract final class DateFormatter {
  static bool _initialized = false;

  static Future<void> initialize() async {
    if (_initialized) return;
    await initializeDateFormatting('id_ID', null);
    _initialized = true;
  }

  /// Formats date as "29 Okt 2026"
  static String formatShortDate(DateTime date) {
    return DateFormat('dd MMM yyyy', 'id_ID').format(date);
  }

  /// Formats date & time as "29 Okt 2026, 09:45 WIB"
  static String formatDateTime(DateTime date) {
    return '${DateFormat('dd MMM yyyy, HH:mm', 'id_ID').format(date)} WIB';
  }

  /// Formats time only as "09:45:12"
  static String formatTime(DateTime date) {
    return DateFormat('HH:mm:ss', 'id_ID').format(date);
  }
}

