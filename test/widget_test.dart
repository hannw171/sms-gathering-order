import 'package:flutter_test/flutter_test.dart';
import 'package:sms_gathering_order/core/utils/date_formatter.dart';
import 'package:sms_gathering_order/main.dart';

void main() {
  testWidgets('SmsGatheringApp renders OnboardingScreen', (WidgetTester tester) async {
    await DateFormatter.initialize();
    await tester.pumpWidget(const SmsGatheringApp());
    await tester.pumpAndSettle();

    expect(find.text('Identifikasi Tamu Apotek'), findsOneWidget);
    expect(find.text('Masuk ke Katalog Promo'), findsOneWidget);
  });
}
