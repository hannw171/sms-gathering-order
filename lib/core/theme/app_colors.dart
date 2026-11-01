import 'package:flutter/material.dart';

/// Semantic Design Colors for SMS Gathering Application
/// Conforms to Flutter Handoff & Token Definitions (Node: 82:2970)
@immutable
class AppColors extends ThemeExtension<AppColors> {
  final Map<String, Color> values;

  const AppColors(this.values);

  // Backgrounds & Surfaces
  Color get backgroundCanvas => values["color/background/canvas"]!;
  Color get surfaceDefault => values["color/surface/default"]!;
  Color get surfaceSubtle => values["color/surface/subtle"]!;

  // Typography & Text
  Color get textPrimary => values["color/text/primary"]!;
  Color get textSecondary => values["color/text/secondary"]!;
  Color get textMuted => values["color/text/muted"]!;

  // Borders & Dividers
  Color get borderDefault => values["color/border/default"]!;
  Color get borderDecorative => values["color/border/decorative"]!;

  // Actions & Buttons (Brand Primary)
  Color get actionPrimary => values["color/action/primary"]!;
  Color get actionOnPrimary => values["color/action/on-primary"]!;
  Color get actionPressed => values["color/action/pressed"]!;
  Color get actionSecondaryContainer => values["color/action/secondary-container"]!;
  Color get actionOnSecondary => values["color/action/on-secondary"]!;

  // Status: Success
  Color get statusSuccess => values["color/status/success"]!;
  Color get statusSuccessContainer => values["color/status/success-container"]!;
  Color get statusOnSuccessContainer => values["color/status/on-success-container"]!;

  // Status: Warning
  Color get statusWarning => values["color/status/warning"]!;
  Color get statusWarningContainer => values["color/status/warning-container"]!;
  Color get statusOnWarningContainer => values["color/status/on-warning-container"]!;

  // Status: Error & Danger
  Color get statusError => values["color/status/error"]!;
  Color get statusErrorContainer => values["color/status/error-container"]!;
  Color get statusOnErrorContainer => values["color/status/on-error-container"]!;

  // Reward, Gamification & Accent (Gold)
  Color get rewardAccent => values["color/reward/accent"]!;
  Color get rewardOnAccent => values["color/reward/on-accent"]!;

  // Raw Brand Constants for Direct Static Access
  static const Color brandPrimary = Color(0xFF1565C0);
  static const Color brandPrimaryDark = Color(0xFF004D99);
  static const Color brandPrimaryLight = Color(0xFFE3F2FD);
  static const Color goldAccent = Color(0xFFFBBF24);
  static const Color emeraldGreen = Color(0xFF047857);

  // Light Mode Color Scheme Mapping
  static const light = AppColors(<String, Color>{
    "color/background/canvas": Color(0xFFFAF8FF), // {color/neutral/canvas}
    "color/surface/default": Color(0xFFFFFFFF), // {color/neutral/white}
    "color/surface/subtle": Color(0xFFF8FAFC), // {color/slate/50}
    "color/text/primary": Color(0xFF131B2E), // {color/ink/900}
    "color/text/secondary": Color(0xFF475569), // {color/slate/600}
    "color/text/muted": Color(0xFF64748B), // {color/slate/500}
    "color/border/default": Color(0xFF64748B), // {color/slate/500}
    "color/border/decorative": Color(0xFFE2E8F0), // {color/slate/200}
    "color/action/primary": Color(0xFF1565C0), // {color/blue/700}
    "color/action/on-primary": Color(0xFFFFFFFF), // {color/neutral/white}
    "color/action/pressed": Color(0xFF004D99), // {color/blue/800}
    "color/action/secondary-container": Color(0xFFE3F2FD), // {color/blue/50}
    "color/action/on-secondary": Color(0xFF004D99), // {color/blue/800}
    "color/status/success": Color(0xFF047857), // {color/emerald/700}
    "color/status/success-container": Color(0xFFECFDF5), // {color/emerald/50}
    "color/status/on-success-container": Color(0xFF047857), // {color/emerald/700}
    "color/status/warning": Color(0xFF6F4500), // {color/amber/900}
    "color/status/warning-container": Color(0xFFFEF3C7), // {color/amber/100}
    "color/status/on-warning-container": Color(0xFF6F4500), // {color/amber/900}
    "color/status/error": Color(0xFFBA1A1A), // {color/red/700}
    "color/status/error-container": Color(0xFFFEF2F2), // {color/red/50}
    "color/status/on-error-container": Color(0xFFBA1A1A), // {color/red/700}
    "color/reward/accent": Color(0xFFFBBF24), // {color/amber/400}
    "color/reward/on-accent": Color(0xFF131B2E), // {color/ink/900}
  });

  // Dark Mode Color Scheme Mapping
  static const dark = AppColors(<String, Color>{
    "color/background/canvas": Color(0xFF0F172A), // {color/slate/900}
    "color/surface/default": Color(0xFF1E293B), // {color/slate/800}
    "color/surface/subtle": Color(0xFF131B2E), // {color/ink/900}
    "color/text/primary": Color(0xFFFFFFFF), // {color/neutral/white}
    "color/text/secondary": Color(0xFFCBD5E1), // {color/slate/300}
    "color/text/muted": Color(0xFFCBD5E1), // {color/slate/300}
    "color/border/default": Color(0xFF94A3B8), // {color/slate/400}
    "color/border/decorative": Color(0xFF334155), // {color/slate/700}
    "color/action/primary": Color(0xFFA9C7FF), // {color/blue/200}
    "color/action/on-primary": Color(0xFF0F172A), // {color/slate/900}
    "color/action/pressed": Color(0xFFE3F2FD), // {color/blue/50}
    "color/action/secondary-container": Color(0xFF334155), // {color/slate/700}
    "color/action/on-secondary": Color(0xFFFFFFFF), // {color/neutral/white}
    "color/status/success": Color(0xFF34D399), // {color/emerald/400}
    "color/status/success-container": Color(0xFF065F46), // {color/emerald/800}
    "color/status/on-success-container": Color(0xFFD1FAE5), // {color/emerald/100}
    "color/status/warning": Color(0xFFFBBF24), // {color/amber/400}
    "color/status/warning-container": Color(0xFF6F4500), // {color/amber/900}
    "color/status/on-warning-container": Color(0xFFFEF3C7), // {color/amber/100}
    "color/status/error": Color(0xFFFECACA), // {color/red/200}
    "color/status/error-container": Color(0xFF93000A), // {color/red/900}
    "color/status/on-error-container": Color(0xFFFECACA), // {color/red/200}
    "color/reward/accent": Color(0xFFFBBF24), // {color/amber/400}
    "color/reward/on-accent": Color(0xFF131B2E), // {color/ink/900}
  });

  @override
  AppColors copyWith({Map<String, Color>? values}) =>
      AppColors(Map.unmodifiable({...this.values, ...?values}));

  @override
  AppColors lerp(covariant AppColors? other, double t) {
    if (other == null) return this;
    return AppColors(Map.unmodifiable({
      for (final key in values.keys)
        key: Color.lerp(values[key], other.values[key], t)!,
    }));
  }
}

/// Convenience context extension for accessing colors in widgets
extension AppColorsExtension on BuildContext {
  AppColors get colors =>
      Theme.of(this).extension<AppColors>() ?? AppColors.light;
}

