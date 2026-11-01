import 'package:flutter/material.dart';
import '../theme/theme.dart';

/// Dynamic Reward Progress Bar (DESIGN.md Section 6.1)
/// Gradient fill from Emerald (#10B981) to Brand Blue (#1565C0)
class RewardProgressBar extends StatelessWidget {
  final double progress;
  final double height;

  const RewardProgressBar({
    super.key,
    required this.progress,
    this.height = 8.0,
  });

  @override
  Widget build(BuildContext context) {
    final clampedProgress = progress.clamp(0.0, 1.0);
    final c = context.colors;

    return ClipRRect(
      borderRadius: AppMetrics.roundedFull,
      child: Container(
        height: height,
        width: double.infinity,
        color: c.borderDecorative,
        child: FractionallySizedBox(
          alignment: Alignment.centerLeft,
          widthFactor: clampedProgress,
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 500),
            curve: Curves.easeOutBack,
            decoration: BoxDecoration(
              borderRadius: AppMetrics.roundedFull,
              gradient: const LinearGradient(
                colors: [
                  Color(0xFF10B981), // Emerald 500
                  Color(0xFF1565C0), // Brand Primary Blue 700
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

