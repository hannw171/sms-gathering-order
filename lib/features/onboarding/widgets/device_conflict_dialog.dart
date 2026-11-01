import 'package:flutter/material.dart';
import '../../../core/theme/theme.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../data/models/pharmacy_model.dart';

/// Modal dialog shown when a pharmacy already has an active device session
/// (Figma Node: 1:77 - Onboarding - Device Conflict Modal)
class DeviceConflictDialog extends StatelessWidget {
  final PharmacyModel pharmacy;
  final VoidCallback onTakeOverSession;

  const DeviceConflictDialog({
    super.key,
    required this.pharmacy,
    required this.onTakeOverSession,
  });

  @override
  Widget build(BuildContext context) {
    final c = context.colors;

    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: AppMetrics.rounded2xl),
      backgroundColor: c.surfaceDefault,
      child: Padding(
        padding: const EdgeInsets.all(AppMetrics.space5),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: c.statusWarningContainer,
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.phonelink_lock_rounded,
                color: c.statusWarning,
                size: 28,
              ),
            ),
            const SizedBox(height: AppMetrics.space4),
            Text(
              'Sesi Apotek Sedang Aktif',
              style: AppTypography.titleLarge(color: c.textPrimary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppMetrics.space2),
            Text(
              'Akun ${pharmacy.name} terdeteksi sedang digunakan pada perangkat lain. Apakah Anda ingin mengambil alih sesi pemesanan di perangkat ini?',
              style: AppTypography.bodyMedium(color: c.textSecondary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppMetrics.space6),
            CustomButton(
              label: 'Ambil Alih Sesi di Perangkat Ini',
              icon: Icons.swap_horiz_rounded,
              onPressed: () {
                Navigator.of(context).pop(true);
                onTakeOverSession();
              },
            ),
            const SizedBox(height: AppMetrics.space2),
            CustomButton(
              label: 'Batal',
              variant: CustomButtonVariant.outline,
              onPressed: () => Navigator.of(context).pop(false),
            ),
          ],
        ),
      ),
    );
  }
}

