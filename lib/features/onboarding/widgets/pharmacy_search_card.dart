import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../data/models/pharmacy_model.dart';

/// Card widget displaying a pharmacy candidate during Onboarding
class PharmacySearchCard extends StatelessWidget {
  final PharmacyModel pharmacy;
  final bool isSelected;
  final VoidCallback onTap;

  const PharmacySearchCard({
    super.key,
    required this.pharmacy,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    return InkWell(
      onTap: onTap,
      borderRadius: AppMetrics.roundedLg,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: AppMetrics.paddingCard,
        decoration: BoxDecoration(
          color: isSelected ? c.actionSecondaryContainer.withValues(alpha: 0.45) : c.surfaceDefault,
          borderRadius: AppMetrics.roundedLg,
          border: Border.all(
            color: isSelected ? c.actionPrimary : c.borderDecorative,
            width: isSelected ? 2.0 : 1.0,
          ),
          boxShadow: AppMetrics.shadowSm,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    pharmacy.name,
                    style: AppTypography.titleMedium(color: c.textPrimary),
                  ),
                ),
                StatusBadge(
                  label: 'Meja ${pharmacy.tableNumber}',
                  type: StatusBadgeType.info,
                ),
              ],
            ),
            const SizedBox(height: AppMetrics.space1),
            Text(
              '${pharmacy.code} • ${pharmacy.ownerName}',
              style: AppTypography.labelLarge(color: c.textSecondary),
            ),
            const SizedBox(height: AppMetrics.space1),
            Text(
              '${pharmacy.address}, ${pharmacy.city}',
              style: AppTypography.bodySmall(color: c.textMuted),
            ),
            if (pharmacy.hasActiveDeviceSession) ...[
              const SizedBox(height: AppMetrics.space2),
              const StatusBadge(
                label: 'Sesi Aktif di Perangkat Lain',
                type: StatusBadgeType.warning,
                icon: Icons.devices_other_rounded,
              ),
            ],
          ],
        ),
      ),
    );
  }
}

