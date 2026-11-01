import 'package:flutter/material.dart';

/// Typography System for SMS Gathering Application
/// Conforms to Figma Handoff (Node: 82:2970) and DESIGN.md specifications
abstract final class AppTypography {
  static const String primaryFont = 'Plus Jakarta Sans';
  static const String displayFont = 'Poppins';

  // Semantic Type Scale
  static TextStyle displaySmall({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 28.0,
        fontWeight: FontWeight.w700,
        height: 36.0 / 28.0,
        letterSpacing: -0.70,
        color: color,
      );

  static TextStyle headlineSmall({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 22.0,
        fontWeight: FontWeight.w700,
        height: 30.0 / 22.0,
        letterSpacing: -0.55,
        color: color,
      );

  static TextStyle titleLarge({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 18.0,
        fontWeight: FontWeight.w600,
        height: 26.0 / 18.0,
        letterSpacing: -0.18,
        color: color,
      );

  static TextStyle titleMedium({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 16.0,
        fontWeight: FontWeight.w600,
        height: 24.0 / 16.0,
        letterSpacing: 0.0,
        color: color,
      );

  static TextStyle bodyLarge({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 15.0,
        fontWeight: FontWeight.w400,
        height: 22.0 / 15.0,
        letterSpacing: 0.0,
        color: color,
      );

  static TextStyle bodyMedium({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 14.0,
        fontWeight: FontWeight.w400,
        height: 20.0 / 14.0,
        letterSpacing: 0.0,
        color: color,
      );

  static TextStyle labelLarge({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 12.0,
        fontWeight: FontWeight.w600,
        height: 18.0 / 12.0,
        letterSpacing: 0.12,
        color: color,
      );

  static TextStyle bodySmall({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 11.0,
        fontWeight: FontWeight.w400,
        height: 16.0 / 11.0,
        letterSpacing: 0.22,
        color: color,
      );

  static TextStyle caption({String fontFamily = primaryFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 10.0,
        fontWeight: FontWeight.w500,
        height: 14.0 / 10.0,
        letterSpacing: 0.25,
        color: color,
      );

  // Price & Nominal Formats (Bold & High Contrast)
  static TextStyle priceLarge({String fontFamily = displayFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 18.0,
        fontWeight: FontWeight.w700,
        height: 24.0 / 18.0,
        letterSpacing: -0.18,
        color: color,
      );

  static TextStyle priceMedium({String fontFamily = displayFont, Color? color}) => TextStyle(
        fontFamily: fontFamily,
        fontSize: 15.0,
        fontWeight: FontWeight.w700,
        height: 20.0 / 15.0,
        letterSpacing: 0.0,
        color: color,
      );

  /// Generates a complete Material 3 TextTheme
  static TextTheme createTextTheme({String fontFamily = primaryFont, Color? baseColor}) {
    return TextTheme(
      displaySmall: displaySmall(fontFamily: fontFamily, color: baseColor),
      headlineSmall: headlineSmall(fontFamily: fontFamily, color: baseColor),
      titleLarge: titleLarge(fontFamily: fontFamily, color: baseColor),
      titleMedium: titleMedium(fontFamily: fontFamily, color: baseColor),
      bodyLarge: bodyLarge(fontFamily: fontFamily, color: baseColor),
      bodyMedium: bodyMedium(fontFamily: fontFamily, color: baseColor),
      labelLarge: labelLarge(fontFamily: fontFamily, color: baseColor),
      bodySmall: bodySmall(fontFamily: fontFamily, color: baseColor),
    );
  }
}

