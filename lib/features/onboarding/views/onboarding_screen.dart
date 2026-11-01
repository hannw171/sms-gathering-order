import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/theme.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../data/models/pharmacy_model.dart';
import '../../catalog/views/catalog_screen.dart';
import '../../leaderboard/views/leaderboard_screen.dart';
import '../providers/onboarding_provider.dart';
import '../widgets/device_conflict_dialog.dart';
import '../widgets/pharmacy_search_card.dart';

/// Guest Identification / Pharmacy Selection Screen (Figma Node: 1:940 & 1:2)
class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _handleContinue(PharmacyModel pharmacy) {
    if (pharmacy.hasActiveDeviceSession) {
      showDialog<bool>(
        context: context,
        builder: (_) => DeviceConflictDialog(
          pharmacy: pharmacy,
          onTakeOverSession: () => _navigateToCatalog(pharmacy),
        ),
      );
      return;
    }
    _navigateToCatalog(pharmacy);
  }

  void _navigateToCatalog(PharmacyModel pharmacy) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => CatalogScreen(pharmacy: pharmacy),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final c = context.colors;
    final provider = context.watch<OnboardingProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'SMS Client Gathering 2026',
              style: AppTypography.labelLarge(color: c.actionPrimary),
            ),
            Text(
              'Identifikasi Tamu Apotek',
              style: AppTypography.titleLarge(color: c.textPrimary),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Papan Peringkat Live',
            icon: Icon(Icons.emoji_events_rounded, color: c.rewardAccent),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const LeaderboardScreen()),
              );
            },
          ),
        ],
      ),
      body: SafeArea(
        child: Padding(
          padding: AppMetrics.paddingScreen,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Cari dan pilih nama apotek Anda untuk memulai pemesanan spesial gathering dan mengumpulkan poin reward.',
                style: AppTypography.bodyMedium(color: c.textSecondary),
              ),
              const SizedBox(height: AppMetrics.space4),
              TextField(
                controller: _searchController,
                onChanged: (val) => provider.loadPharmacies(query: val),
                decoration: InputDecoration(
                  hintText: 'Cari nama apotek, kode pelanggan, atau kota...',
                  prefixIcon: Icon(Icons.search_rounded, color: c.textMuted),
                  suffixIcon: _searchController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear_rounded),
                          onPressed: () {
                            _searchController.clear();
                            provider.loadPharmacies(query: '');
                          },
                        )
                      : null,
                ),
              ),
              const SizedBox(height: AppMetrics.space4),
              Expanded(
                child: provider.isLoading
                    ? const Center(child: CircularProgressIndicator())
                    : provider.pharmacies.isEmpty
                        ? _buildEmptyState(context)
                        : ListView.separated(
                            itemCount: provider.pharmacies.length,
                            separatorBuilder: (_, _) =>
                                const SizedBox(height: AppMetrics.space3),
                            itemBuilder: (context, index) {
                              final item = provider.pharmacies[index];
                              final isSelected =
                                  provider.selectedPharmacy?.id == item.id;
                              return PharmacySearchCard(
                                pharmacy: item,
                                isSelected: isSelected,
                                onTap: () => provider.selectPharmacy(item),
                              );
                            },
                          ),
              ),
              const SizedBox(height: AppMetrics.space4),
              CustomButton(
                label: 'Masuk ke Katalog Promo',
                icon: Icons.arrow_forward_rounded,
                onPressed: provider.selectedPharmacy == null
                    ? null
                    : () => _handleContinue(provider.selectedPharmacy!),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    final c = context.colors;
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.store_mall_directory_outlined, size: 56, color: c.textMuted),
          const SizedBox(height: AppMetrics.space3),
          Text(
            'Data Apotek Tidak Ditemukan',
            style: AppTypography.titleMedium(color: c.textPrimary),
          ),
          const SizedBox(height: AppMetrics.space1),
          Text(
            'Silakan periksa kembali kata kunci pencarian atau hubungi meja registrasi panitia.',
            style: AppTypography.bodyMedium(color: c.textSecondary),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

