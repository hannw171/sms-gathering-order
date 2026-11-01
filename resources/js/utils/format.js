/**
 * Format number into Indonesian Rupiah (IDR)
 * e.g. 1450000 -> "Rp 1.450.000"
 */
export function formatRupiah(amount) {
  if (amount === undefined || amount === null || isNaN(amount)) {
    return 'Rp 0';
  }
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
}

/**
 * Format remaining seconds into HH:MM:SS or MM:SS
 */
export function formatCountdown(targetDate) {
  if (!targetDate) return '00:00:00';
  const totalSeconds = Math.max(0, Math.floor((new Date(targetDate) - new Date()) / 1000));
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  return [
    hours.toString().padStart(2, '0'),
    minutes.toString().padStart(2, '0'),
    seconds.toString().padStart(2, '0'),
  ].join(':');
}
