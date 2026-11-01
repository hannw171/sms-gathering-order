import 'package:flutter/material.dart';
import '../theme/theme.dart';

enum CustomButtonVariant { primary, secondary, outline, danger }

/// Reusable CTA button adhering to SMS Gathering Design Tokens
class CustomButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final CustomButtonVariant variant;
  final IconData? icon;
  final bool isLoading;
  final bool isFullWidth;

  const CustomButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.variant = CustomButtonVariant.primary,
    this.icon,
    this.isLoading = false,
    this.isFullWidth = true,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    Color bgColor;
    Color fgColor;
    BorderSide borderSide = BorderSide.none;

    switch (variant) {
      case CustomButtonVariant.primary:
        bgColor = c.actionPrimary;
        fgColor = c.actionOnPrimary;
        break;
      case CustomButtonVariant.secondary:
        bgColor = c.actionSecondaryContainer;
        fgColor = c.actionOnSecondary;
        break;
      case CustomButtonVariant.outline:
        bgColor = Colors.transparent;
        fgColor = c.actionPrimary;
        borderSide = BorderSide(color: c.actionPrimary, width: 1.5);
        break;
      case CustomButtonVariant.danger:
        bgColor = c.statusError;
        fgColor = Colors.white;
        break;
    }

    final child = isLoading
        ? SizedBox(
            width: 20,
            height: 20,
            child: CircularProgressIndicator(
              strokeWidth: 2.0,
              valueColor: AlwaysStoppedAnimation<Color>(fgColor),
            ),
          )
        : Row(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (icon != null) ...[
                Icon(icon, size: 18, color: fgColor),
                const SizedBox(width: AppMetrics.space2),
              ],
              Text(
                label,
                style: AppTypography.titleMedium(color: fgColor),
              ),
            ],
          );

    return SizedBox(
      width: isFullWidth ? double.infinity : null,
      height: AppMetrics.minButtonHeight,
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: bgColor,
          foregroundColor: fgColor,
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: AppMetrics.roundedMd,
            side: borderSide,
          ),
        ),
        child: child,
      ),
    );
  }
}

