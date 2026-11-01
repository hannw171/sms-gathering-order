import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'core/constants/app_config.dart';
import 'core/providers/connectivity_provider.dart';
import 'core/services/network_quality_service.dart';
import 'core/theme/theme.dart';
import 'core/utils/date_formatter.dart';
import 'data/services/api_service.dart';
import 'features/cart/providers/cart_provider.dart';
import 'features/catalog/providers/catalog_provider.dart';
import 'features/leaderboard/providers/leaderboard_provider.dart';
import 'features/onboarding/providers/onboarding_provider.dart';
import 'features/onboarding/views/onboarding_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
      statusBarBrightness: Brightness.light,
    ),
  );
  await DateFormatter.initialize();
  runApp(const SmsGatheringApp());
}

class SmsGatheringApp extends StatelessWidget {
  final ApiService? apiService;

  const SmsGatheringApp({
    super.key,
    this.apiService,
  });

  @override
  Widget build(BuildContext context) {
    final effectiveApiService = apiService ?? ApiService();

    return MultiProvider(
      providers: [
        Provider<ApiService>.value(value: effectiveApiService),
        ChangeNotifierProvider<ConnectivityProvider>(
          create: (_) => ConnectivityProvider(
            networkService: NetworkQualityService(
              apiService: effectiveApiService,
            ),
          ),
        ),
        ChangeNotifierProvider<OnboardingProvider>(
          create: (_) => OnboardingProvider(
            apiService: effectiveApiService,
          ),
        ),
        ChangeNotifierProvider<CatalogProvider>(
          create: (_) => CatalogProvider(
            apiService: effectiveApiService,
          ),
        ),
        ChangeNotifierProvider<CartProvider>(
          create: (_) => CartProvider(
            apiService: effectiveApiService,
          ),
        ),
        ChangeNotifierProvider<LeaderboardProvider>(
          create: (_) => LeaderboardProvider(
            apiService: effectiveApiService,
          ),
        ),
      ],
      child: MaterialApp(
        title: AppConfig.appName,
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        darkTheme: AppTheme.darkTheme,
        themeMode: ThemeMode.light,
        home: const OnboardingScreen(),
      ),
    );
  }
}
