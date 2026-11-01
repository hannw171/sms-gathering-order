import 'package:flutter/material.dart';

/// Primitive tokens directly extracted from Figma design handoff
/// (SMS-Gathering-APP, Node: 82:2970)
abstract final class AppPrimitives {
  static const colors = <String, Color>{
    // Neutral
    "color/neutral/white": Color(0xFFFFFFFF),
    "color/neutral/canvas": Color(0xFFFAF8FF),
    "color/neutral/black": Color(0xFF000000),

    // Blue / Primary Brand
    "color/blue/50": Color(0xFFE3F2FD),
    "color/blue/200": Color(0xFFA9C7FF),
    "color/blue/700": Color(0xFF1565C0),
    "color/blue/800": Color(0xFF004D99),
    "color/blue/850": Color(0xFF00468C),
    "color/blue/900": Color(0xFF0D47A1),

    // Ink & Navy
    "color/ink/900": Color(0xFF131B2E),
    "color/ink/950": Color(0xFF090D16),
    "color/navy/950": Color(0xFF0B1C30),

    // Slate / Grayscale
    "color/slate/50": Color(0xFFF8FAFC),
    "color/slate/100": Color(0xFFF1F5F9),
    "color/slate/200": Color(0xFFE2E8F0),
    "color/slate/300": Color(0xFFCBD5E1),
    "color/slate/400": Color(0xFF94A3B8),
    "color/slate/500": Color(0xFF64748B),
    "color/slate/600": Color(0xFF475569),
    "color/slate/700": Color(0xFF334155),
    "color/slate/800": Color(0xFF1E293B),
    "color/slate/900": Color(0xFF0F172A),

    // Emerald / Success
    "color/emerald/50": Color(0xFFECFDF5),
    "color/emerald/100": Color(0xFFD1FAE5),
    "color/emerald/400": Color(0xFF34D399),
    "color/emerald/500": Color(0xFF10B981),
    "color/emerald/600": Color(0xFF059669),
    "color/emerald/700": Color(0xFF047857),
    "color/emerald/800": Color(0xFF065F46),

    // Amber / Warning & Rewards (Gold)
    "color/amber/50": Color(0xFFFFFBEB),
    "color/amber/100": Color(0xFFFEF3C7),
    "color/amber/200": Color(0xFFFDE68A),
    "color/amber/300": Color(0xFFFCD34D),
    "color/amber/400": Color(0xFFFBBF24),
    "color/amber/500": Color(0xFFF59E0B),
    "color/amber/600": Color(0xFFD97706),
    "color/amber/700": Color(0xFFB45309),
    "color/amber/900": Color(0xFF6F4500),

    // Red / Error & Danger
    "color/red/50": Color(0xFFFEF2F2),
    "color/red/200": Color(0xFFFECACA),
    "color/red/600": Color(0xFFDC2626),
    "color/red/700": Color(0xFFBA1A1A),
    "color/red/900": Color(0xFF93000A),

    // Supplementary tints
    "color/source/01": Color(0xFF565E74),
    "color/source/02": Color(0xFFE2E7FF),
    "color/source/03": Color(0xFF6B7280),
    "color/source/04": Color(0xFF424752),
    "color/source/05": Color(0xFFEAEDFF),
    "color/source/06": Color(0xFFF2F3FF),
    "color/source/07": Color(0xFFFFDAD6),
    "color/source/08": Color(0xFF727783),
    "color/source/09": Color(0xFFDAE2FD),
    "color/source/10": Color(0xFF213145),
    "color/source/11": Color(0xFFEEF0FF),
    "color/source/12": Color(0xFFF8F9FF),
    "color/source/13": Color(0xFF00855B),
    "color/source/14": Color(0xFFF5FFF6),
    "color/source/15": Color(0xFF006947),
    "color/source/16": Color(0xFFE5EEFF),
    "color/source/17": Color(0xFFEFF4FF),
    "color/source/18": Color(0xFF3D4947),
    "color/source/19": Color(0xFFEAF1FF),
    "color/source/20": Color(0xFFCBDBF5),
    "color/source/21": Color(0xFFD6E3FF),
    "color/source/22": Color(0xFFBFDBFE),
    "color/source/23": Color(0xFFDBEAFE),
    "color/source/24": Color(0xFFA7F3D0),
    "color/source/25": Color(0xFF93C5FD),
    "color/source/26": Color(0xFF92400E),
    "color/source/27": Color(0xFFEFF6FF),
    "color/source/28": Color(0xFF006C49),
    "color/source/29": Color(0xFF451A03),
  };

  static const numbers = <String, double>{
    // Spacing
    "spacing/0": 0.0,
    "spacing/1": 1.0,
    "spacing/2": 2.0,
    "spacing/3": 3.0,
    "spacing/4": 4.0,
    "spacing/6": 6.0,
    "spacing/7": 7.0,
    "spacing/8": 8.0,
    "spacing/10": 10.0,
    "spacing/12": 12.0,
    "spacing/14": 14.0,
    "spacing/16": 16.0,
    "spacing/20": 20.0,
    "spacing/24": 24.0,
    "spacing/28": 28.0,
    "spacing/32": 32.0,
    "spacing/44": 44.0,
    "spacing/48": 48.0,
    "spacing/64": 64.0,
    "spacing/96": 96.0,
    "spacing/112": 112.0,

    // Radius
    "radius/0": 0.0,
    "radius/4": 4.0,
    "radius/6": 6.0,
    "radius/8": 8.0,
    "radius/12": 12.0,
    "radius/16": 16.0,
    "radius/24": 24.0,
    "radius/9999": 9999.0,

    // Font Sizes
    "font-size/9": 9.0,
    "font-size/10": 10.0,
    "font-size/11": 11.0,
    "font-size/12": 12.0,
    "font-size/13": 13.0,
    "font-size/14": 14.0,
    "font-size/15": 15.0,
    "font-size/16": 16.0,
    "font-size/18": 18.0,
    "font-size/22": 22.0,
    "font-size/28": 28.0,

    // Opacity
    "opacity/0": 0.0,
    "opacity/0-05": 0.05,
    "opacity/0-1": 0.1,
    "opacity/0-2": 0.2,
    "opacity/0-25": 0.25,
    "opacity/0-4": 0.4,
    "opacity/0-5": 0.5,
    "opacity/0-6": 0.6,
    "opacity/0-65": 0.65,
    "opacity/0-7": 0.7,
    "opacity/0-8": 0.8,
    "opacity/0-85": 0.85,
    "opacity/0-9": 0.9,
    "opacity/0-95": 0.95,
    "opacity/1": 1.0,

    // Stroke Width
    "stroke-width/1": 1.0,
    "stroke-width/2": 2.0,
  };
}

