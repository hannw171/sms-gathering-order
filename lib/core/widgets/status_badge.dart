import 'package:flutter/material.dart';
import '../theme/theme.dart';

enum StatusBadgeType { success, warning, error, info, reward }

/// Semantic Status Badge following Figma & DESIGN.md Section 11.1
class StatusBadge extends StatelessWidget {
  final String label;
  final StatusBadgeType type;
  final IconData? icon;

  const StatusBadge({
    super.key,
    required this.label,
    this.type = StatusBadgeType.info,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    late final Color bgColor;
    late final Color fgColor;

    switch (type) {
      case StatusBadgeType.success:
        bgColor = c.statusSuccessContainer;
        fgColor = c.statusOnSuccessContainer;
        break;
      case StatusBadgeType.warning:
        bgColor = c.statusWarningContainer;
        fgColor = c.statusOnWarningContainer;
        break;
      case StatusBadgeType.error:
        bgColor = c.statusErrorContainer;
        fgColor = c.statusOnErrorContainer;
        break;
      case StatusBadgeType.reward:
        bgColor = c.rewardAccent;
        fgColor = c.rewardOnAccent;
        break;
      case StatusBadgeType.info:
        bgColor = c.actionSecondaryContainer;
        fgColor = c.actionOnSecondary;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: AppMetrics.space2,
        vertical: AppMetrics.space1,
      ),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: AppMetrics.roundedFull,
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 14, color: fgColor),
            const SizedBox(width: AppMetrics.space1),
          ],
          Text(
            label,
            style: AppTypography.labelLarge(color: fgColor),
          ),
        ],
      ),
    );
  }
}

