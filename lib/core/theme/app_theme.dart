import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'app_colors.dart';
import 'app_metrics.dart';
import 'app_typography.dart';

/// Complete Flutter Theme configuration for SMS Gathering App
/// Conforms to Material 3 and Figma handoff specifications (Node: 82:2970)
abstract final class AppTheme {
  /// Builds ThemeData for the specified brightness and optional font family
  static ThemeData build(Brightness brightness, {String fontFamily = AppTypography.primaryFont}) {
    final isDark = brightness == Brightness.dark;
    final c = isDark ? AppColors.dark : AppColors.light;

    final colorScheme = ColorScheme(
      brightness: brightness,
      primary: c.actionPrimary,
      onPrimary: c.actionOnPrimary,
      primaryContainer: c.actionSecondaryContainer,
      onPrimaryContainer: c.actionOnSecondary,
      secondary: c.rewardAccent,
      onSecondary: c.rewardOnAccent,
      error: c.statusError,
      onError: isDark ? c.statusOnErrorContainer : Colors.white,
      errorContainer: c.statusErrorContainer,
      onErrorContainer: c.statusOnErrorContainer,
      surface: c.surfaceDefault,
      onSurface: c.textPrimary,
      onSurfaceVariant: c.textSecondary,
      outline: c.borderDefault,
      outlineVariant: c.borderDecorative,
    );

    final textTheme = AppTypography.createTextTheme(
      fontFamily: fontFamily,
      baseColor: c.textPrimary,
    );

    return ThemeData(
      useMaterial3: true,
      brightness: brightness,
      colorScheme: colorScheme,
      fontFamily: fontFamily,
      scaffoldBackgroundColor: c.backgroundCanvas,
      textTheme: textTheme,
      extensions: <ThemeExtension<dynamic>>[c],

      // App Bar Theme
      appBarTheme: AppBarTheme(
        elevation: 0,
        scrolledUnderElevation: 0,
        backgroundColor: c.surfaceDefault,
        foregroundColor: c.textPrimary,
        centerTitle: false,
        titleTextStyle: AppTypography.titleLarge(fontFamily: fontFamily, color: c.textPrimary),
        iconTheme: IconThemeData(color: c.textPrimary, size: 24),
        systemOverlayStyle: SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: isDark ? Brightness.light : Brightness.dark,
          statusBarBrightness: isDark ? Brightness.dark : Brightness.light,
        ),
      ),

      // Card Theme
      cardTheme: CardThemeData(
        color: c.surfaceDefault,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppMetrics.radiusCard),
          side: BorderSide(color: c.borderDecorative, width: 1.0),
        ),
        margin: EdgeInsets.zero,
        clipBehavior: Clip.antiAlias,
      ),

      // Filled Button Theme (Primary CTA)
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: c.actionPrimary,
          foregroundColor: c.actionOnPrimary,
          minimumSize: const Size.fromHeight(AppMetrics.minButtonHeight),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppMetrics.radiusControl),
          ),
          textStyle: AppTypography.titleMedium(
            fontFamily: fontFamily,
            color: c.actionOnPrimary,
          ),
          elevation: 0,
        ),
      ),

      // Elevated Button Theme
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: c.actionPrimary,
          foregroundColor: c.actionOnPrimary,
          minimumSize: const Size.fromHeight(AppMetrics.ctaButtonHeight),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppMetrics.radiusControl),
          ),
          textStyle: AppTypography.titleMedium(
            fontFamily: fontFamily,
            color: c.actionOnPrimary,
          ),
          elevation: 0,
        ),
      ),

      // Outlined Button Theme
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: c.actionPrimary,
          minimumSize: const Size.fromHeight(AppMetrics.minButtonHeight),
          side: BorderSide(color: c.actionPrimary, width: 1.5),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppMetrics.radiusControl),
          ),
          textStyle: AppTypography.titleMedium(
            fontFamily: fontFamily,
            color: c.actionPrimary,
          ),
        ),
      ),

      // Form & Input Decoration Theme
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: c.surfaceDefault,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: AppMetrics.space4,
          vertical: 14.0,
        ),
        hintStyle: AppTypography.bodyMedium(fontFamily: fontFamily, color: c.textMuted),
        labelStyle: AppTypography.bodyMedium(fontFamily: fontFamily, color: c.textSecondary),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppMetrics.radiusControl),
          borderSide: BorderSide(color: c.borderDecorative, width: 1.0),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppMetrics.radiusControl),
          borderSide: BorderSide(color: c.borderDecorative, width: 1.0),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppMetrics.radiusControl),
          borderSide: BorderSide(color: c.actionPrimary, width: 2.0),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppMetrics.radiusControl),
          borderSide: BorderSide(color: c.statusError, width: 1.5),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppMetrics.radiusControl),
          borderSide: BorderSide(color: c.statusError, width: 2.0),
        ),
      ),

      // Divider Theme
      dividerTheme: DividerThemeData(
        color: c.borderDecorative,
        thickness: 1.0,
        space: 1.0,
      ),

      // Dialog Theme
      dialogTheme: DialogThemeData(
        backgroundColor: c.surfaceDefault,
        elevation: 6,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppMetrics.radiusDialog),
        ),
      ),

      // Bottom Sheet Theme
      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor: c.surfaceDefault,
        modalBackgroundColor: c.surfaceDefault,
        elevation: 8,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppMetrics.radius3xl)),
        ),
      ),
    );
  }

  /// Light theme shortcut
  static ThemeData get lightTheme => build(Brightness.light);

  /// Dark theme shortcut
  static ThemeData get darkTheme => build(Brightness.dark);
}

