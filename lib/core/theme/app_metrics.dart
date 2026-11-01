import 'package:flutter/material.dart';

/// Layout Metrics, Spacing, and Radius tokens
/// Conforms to Figma Handoff (Node: 82:2970) & DESIGN.md specifications
abstract final class AppMetrics {
  // Primary semantic metrics from Figma
  static const double spacingControlGap = 8.0;
  static const double spacingCardPadding = 16.0;
  static const double spacingSectionGap = 24.0;

  static const double radiusControl = 8.0;
  static const double radiusCard = 12.0;
  static const double radiusDialog = 16.0;
  static const double radiusPill = 9999.0; // Sentinel value; prefer StadiumBorder() where applicable

  // Ergonomics & Accessibility
  static const double minTouchTarget = 48.0; // Mobile touch target standard
  static const double minButtonHeight = 48.0;
  static const double ctaButtonHeight = 52.0;
  static const double inputHeight = 48.0;
  static const double floatingCartHeight = 56.0;

  // 4px / 8px Grid Spacing Scale (DESIGN.md)
  static const double space0 = 0.0;
  static const double space1 = 4.0;
  static const double space2 = 8.0;
  static const double space3 = 12.0;
  static const double space4 = 16.0; // Standard base padding
  static const double space5 = 20.0;
  static const double space6 = 24.0;
  static const double space8 = 32.0;
  static const double space12 = 48.0;
  static const double space16 = 64.0;

  // Border Radius Scale
  static const double radiusNone = 0.0;
  static const double radiusXs = 4.0;
  static const double radiusSm = 6.0;
  static const double radiusMd = 8.0;
  static const double radiusLg = 12.0;
  static const double radiusXl = 16.0;
  static const double radius2xl = 20.0;
  static const double radius3xl = 24.0;
  static const double radiusFull = 9999.0;

  // Pre-built BorderRadius shortcuts
  static final BorderRadius roundedXs = BorderRadius.circular(radiusXs);
  static final BorderRadius roundedSm = BorderRadius.circular(radiusSm);
  static final BorderRadius roundedMd = BorderRadius.circular(radiusMd);
  static final BorderRadius roundedLg = BorderRadius.circular(radiusLg);
  static final BorderRadius roundedXl = BorderRadius.circular(radiusXl);
  static final BorderRadius rounded2xl = BorderRadius.circular(radius2xl);
  static final BorderRadius rounded3xl = BorderRadius.circular(radius3xl);
  static final BorderRadius roundedFull = BorderRadius.circular(radiusFull);

  // Pre-built EdgeInsets shortcuts
  static const EdgeInsets paddingScreen = EdgeInsets.symmetric(horizontal: space4, vertical: space4);
  static const EdgeInsets paddingCard = EdgeInsets.all(space4);
  static const EdgeInsets paddingCardSm = EdgeInsets.all(space3);

  // Elevation & Shadows (Figma & DESIGN.md)
  static List<BoxShadow> get shadowSm => [
        BoxShadow(
          color: const Color(0xFF0F172A).withValues(alpha: 0.05),
          blurRadius: 2,
          offset: const Offset(0, 1),
        ),
      ];

  static List<BoxShadow> get shadowMd => [
        BoxShadow(
          color: const Color(0xFF0F172A).withValues(alpha: 0.08),
          blurRadius: 6,
          spreadRadius: -1,
          offset: const Offset(0, 4),
        ),
        BoxShadow(
          color: const Color(0xFF0F172A).withValues(alpha: 0.04),
          blurRadius: 4,
          spreadRadius: -2,
          offset: const Offset(0, 2),
        ),
      ];

  static List<BoxShadow> get shadowFloating => [
        BoxShadow(
          color: const Color(0xFF0F172A).withValues(alpha: 0.15),
          blurRadius: 25,
          spreadRadius: -3,
          offset: const Offset(0, 10),
        ),
        BoxShadow(
          color: const Color(0xFF0F172A).withValues(alpha: 0.10),
          blurRadius: 6,
          spreadRadius: -4,
          offset: const Offset(0, 4),
        ),
      ];

  static List<BoxShadow> get shadowDrawer => [
        BoxShadow(
          color: const Color(0xFF0F172A).withValues(alpha: 0.12),
          blurRadius: 30,
          offset: const Offset(0, -8),
        ),
      ];
}

