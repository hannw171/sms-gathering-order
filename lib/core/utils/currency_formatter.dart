import 'package:intl/intl.dart';

/// Indonesian Rupiah (IDR) currency formatter utility
abstract final class CurrencyFormatter {
  static final NumberFormat _idrFormat = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  static final NumberFormat _compactFormat = NumberFormat.compactCurrency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 1,
  );

  /// Formats number into standard Indonesian Rupiah (e.g., "Rp 1.250.000")
  static String format(num amount) {
    return _idrFormat.format(amount);
  }

  /// Formats number into compact Indonesian Rupiah (e.g., "Rp 12,5 Jt")
  static String formatCompact(num amount) {
    return _compactFormat.format(amount);
  }
}

