import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah } from '../../utils/format';
import { useCart } from '../../hooks/useCart';
import { Trash2, Plus, Minus, ArrowLeft, Send, Sparkles, AlertCircle, ShoppingBag } from 'lucide-react';

export default function Cart({ pharmacy, milestone, event, errors }) {
  const { cartItems, totalItems, totalAmount, updateQuantity, clearCart } = useCart();
  const [notes, setNotes] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showConfirmModal, setShowConfirmModal] = useState(false);

  // Projected spending after this order
  const currentSpending = milestone?.total_spending || 0;
  const projectedTotal = currentSpending + totalAmount;

  // Find projected tier
  const allTiers = milestone?.all_tiers || [];
  let projectedTier = null;
  for (const t of allTiers) {
    if (projectedTotal >= parseFloat(t.min_spending)) {
      projectedTier = t;
    }
  }

  const handleConfirmOrder = () => {
    setIsSubmitting(true);
    router.post('/checkout', {
      items: cartItems.map((item) => ({
        product_id: item.product_id,
        quantity: item.quantity,
      })),
      notes: notes,
    }, {
      onSuccess: () => {
        clearCart();
      },
      onFinish: () => {
        setIsSubmitting(false);
        setShowConfirmModal(false);
      },
    });
  };

  return (
    <GuestLayout pharmacy={pharmacy} milestone={milestone} title="Keranjang Belanja">
      <Head title="Keranjang Belanja & Review Reward" />

      {/* Cart Top Bar */}
      <div className="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
        <Link href="/katalog" className="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-900">
          <ArrowLeft className="w-4 h-4" />
          <span>Kembali ke Katalog</span>
        </Link>
        <span className="text-xs font-bold text-slate-700">
          {totalItems} Item Dipilih
        </span>
      </div>

      <div className="p-4 space-y-4">
        {cartItems.length === 0 ? (
          <div className="bg-white rounded-2xl p-8 text-center border border-slate-200/90 my-8 shadow-sm">
            <ShoppingBag className="w-12 h-12 text-slate-300 mx-auto mb-3" />
            <h3 className="font-bold text-base text-slate-800 mb-1">Keranjang Belanja Kosong</h3>
            <p className="text-xs text-slate-500 mb-5">
              Belum ada obat yang dimasukkan ke dalam keranjang belanja Anda.
            </p>
            <Link
              href="/katalog"
              className="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-sm transition-colors"
            >
              Lihat Katalog Sekarang
            </Link>
          </div>
        ) : (
          <>
            {/* Projected Milestone Unlocked Banner (Figma node 1:735) */}
            <div className="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-4 rounded-2xl shadow-md relative overflow-hidden">
              <div className="flex items-center gap-2 text-xs font-bold text-emerald-200 uppercase tracking-wide mb-1">
                <Sparkles className="w-4 h-4 text-amber-300" />
                <span>Simulasi Reward Pesanan Ini</span>
              </div>
              <p className="text-xs text-emerald-50 leading-relaxed">
                Dengan menyelesaikan order ini, total belanja akumulasi Anda menjadi{' '}
                <strong className="text-amber-300 font-bold">{formatRupiah(projectedTotal)}</strong>.
              </p>
              {projectedTier && (
                <div className="mt-2.5 inline-flex items-center gap-2 bg-white/15 backdrop-blur-md border border-white/20 rounded-xl px-3 py-1.5 text-xs font-bold text-white shadow-inner">
                  <span>{projectedTier.badge_icon}</span>
                  <span>Membuka Reward: {projectedTier.reward_description}</span>
                </div>
              )}
            </div>

            {/* Cart Items List */}
            <div className="bg-white rounded-2xl border border-slate-200/90 shadow-xs divide-y divide-slate-100 overflow-hidden">
              {cartItems.map((item) => (
                <div key={item.product_id} className="p-3.5 flex items-center justify-between gap-3">
                  <div className="flex-1 min-w-0">
                    <span className="text-[10px] font-bold text-brand-600 block uppercase tracking-wider">
                      {item.manufacturer_name}
                    </span>
                    <h4 className="font-semibold text-xs text-slate-900 truncate">
                      {item.name}
                    </h4>
                    <span className="text-[11px] text-slate-500 block">
                      {formatRupiah(item.price)} &times; {item.quantity} ({item.uom})
                    </span>
                    <span className="text-xs font-bold text-brand-700 mt-0.5 block">
                      {formatRupiah(item.price * item.quantity)}
                    </span>
                  </div>

                  {/* Quantity Stepper & Remove */}
                  <div className="flex items-center gap-2 shrink-0">
                    <div className="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                      <button
                        type="button"
                        onClick={() => updateQuantity({ id: item.product_id }, item.quantity - 1)}
                        className="w-6 h-6 rounded-lg bg-white flex items-center justify-center text-slate-700 hover:bg-slate-200 shadow-xs transition-colors"
                      >
                        <Minus className="w-3 h-3" />
                      </button>
                      <span className="w-6 text-center text-xs font-bold text-slate-800">
                        {item.quantity}
                      </span>
                      <button
                        type="button"
                        onClick={() => updateQuantity({ id: item.product_id }, item.quantity + 1)}
                        className="w-6 h-6 rounded-lg bg-brand-600 flex items-center justify-center text-white hover:bg-brand-700 shadow-xs transition-colors"
                      >
                        <Plus className="w-3 h-3" />
                      </button>
                    </div>

                    <button
                      type="button"
                      onClick={() => updateQuantity({ id: item.product_id }, 0)}
                      className="p-1.5 text-slate-400 hover:text-red-500 transition-colors"
                      title="Hapus"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              ))}
            </div>

            {/* Notes Field */}
            <div className="bg-white rounded-2xl p-3.5 border border-slate-200/90 shadow-xs">
              <label htmlFor="notes" className="block text-xs font-semibold text-slate-700 mb-1.5">
                Catatan Tambahan untuk Sales PIC / Panitia:
              </label>
              <textarea
                id="notes"
                rows={2}
                placeholder="Contoh: Pengiriman harap dijadikan satu, konfirmasi via WA..."
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
                className="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600"
              />
            </div>

            {/* Order Summary Box */}
            <div className="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2">
              <h4 className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">
                Ringkasan Transaksi
              </h4>
              <div className="flex justify-between text-xs text-slate-600">
                <span>Total Kuantitas Obat</span>
                <span className="font-semibold">{totalItems} Kemasan</span>
              </div>
              <div className="flex justify-between text-xs text-slate-600">
                <span>Total Belanja Akumulasi Sebelumnya</span>
                <span className="font-semibold">{formatRupiah(currentSpending)}</span>
              </div>
              <div className="pt-2 border-t border-slate-100 flex justify-between items-baseline">
                <span className="text-xs font-bold text-slate-900">Total Nilai PO Ini</span>
                <span className="text-lg font-bold text-brand-700">{formatRupiah(totalAmount)}</span>
              </div>
            </div>

            {/* Submit CTA Button */}
            <button
              type="button"
              onClick={() => setShowConfirmModal(true)}
              disabled={isSubmitting || totalItems === 0}
              className="w-full bg-brand-600 hover:bg-brand-700 active:bg-brand-800 disabled:opacity-50 text-white font-bold py-3.5 px-4 rounded-2xl shadow-floating flex items-center justify-center gap-2 text-sm transition-all"
            >
              <Send className="w-4 h-4" />
              <span>Kirim Pesanan Sekarang ({formatRupiah(totalAmount)})</span>
            </button>
          </>
        )}
      </div>

      {/* Confirmation Modal */}
      {showConfirmModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-xs w-full p-5 shadow-2xl border border-slate-200 text-center animate-in fade-in zoom-in duration-150">
            <div className="w-12 h-12 rounded-full bg-brand-100 text-brand-600 mx-auto flex items-center justify-center mb-3">
              <Send className="w-6 h-6" />
            </div>
            <h3 className="text-base font-bold text-slate-900 mb-1">
              Konfirmasi Pengiriman PO
            </h3>
            <p className="text-xs text-slate-500 mb-4 leading-relaxed">
              Anda akan mengirimkan pesanan senilai <strong className="text-slate-800">{formatRupiah(totalAmount)}</strong> untuk Apotek <strong>{pharmacy?.name}</strong>. Pesanan yang dikirim akan langsung dihitung di papan peringkat!
            </p>
            <div className="flex flex-col gap-2">
              <button
                type="button"
                onClick={handleConfirmOrder}
                disabled={isSubmitting}
                className="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition-colors shadow-sm"
              >
                {isSubmitting ? 'Mengirim...' : 'Ya, Kirim Pesanan'}
              </button>
              <button
                type="button"
                onClick={() => setShowConfirmModal(false)}
                className="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium py-2.5 px-4 rounded-xl text-xs transition-colors"
              >
                Periksa Lagi
              </button>
            </div>
          </div>
        </div>
      )}
    </GuestLayout>
  );
}
