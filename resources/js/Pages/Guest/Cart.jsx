import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import axios from 'axios';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah } from '../../utils/format';
import { useCart } from '../../hooks/useCart';
import QuantityStepper from '../../Components/QuantityStepper';
import {
  Trash2,
  ArrowLeft,
  Send,
  Sparkles,
  Gift,
  CheckCircle,
  ShoppingBag,
  HelpCircle,
} from 'lucide-react';

export default function Cart({
  pharmacy,
  milestone,
  currentRewards,
  rewardOptions,
  event,
  errors,
}) {
  const { cartItems, totalItems, totalAmount, updateQuantity, removeItem, clearCart } = useCart();
  const [notes, setNotes] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showConfirmModal, setShowConfirmModal] = useState(false);

  // Choice selections
  const interbatChoices = rewardOptions?.interbat || ['Elektrik Kettle', 'Panci Kuah', 'Panci Mini Listrik'];
  const grahaNeoChoices = rewardOptions?.graha_neo || ['Magic Com Cosmos', 'Mixer Miyako'];
  const [selectedInterbat, setSelectedInterbat] = useState(interbatChoices[0]);
  const [selectedGrahaNeo, setSelectedGrahaNeo] = useState(grahaNeoChoices[0]);

  // Real-time server reward preview
  const [preview, setPreview] = useState(null);
  const [isLoadingPreview, setIsLoadingPreview] = useState(false);

  useEffect(() => {
    if (cartItems.length === 0) {
      setPreview(null);
      return;
    }

    let isMounted = true;
    setIsLoadingPreview(true);

    const timer = setTimeout(() => {
      axios
        .post('/keranjang/preview-rewards', { items: cartItems })
        .then((res) => {
          if (isMounted) {
            setPreview(res.data);
          }
        })
        .catch(() => {})
        .finally(() => {
          if (isMounted) {
            setIsLoadingPreview(false);
          }
        });
    }, 250);

    return () => {
      isMounted = false;
      clearTimeout(timer);
    };
  }, [cartItems]);

  const unlockedNow = preview?.unlocked_now || {};
  const hasInterbatChoice = (unlockedNow.interbat_choice_count || 0) > 0;
  const hasGrahaNeoChoice = (unlockedNow.graha_neo_choice_count || 0) > 0;

  // Projected spending after this order
  const currentSpending = milestone?.total_spending || 0;
  const projectedTotal = currentSpending + totalAmount;

  const handleConfirmOrder = () => {
    setIsSubmitting(true);
    const rewardSelections = {};
    if (hasInterbatChoice) {
      rewardSelections.interbat = selectedInterbat;
    }
    if (hasGrahaNeoChoice) {
      rewardSelections.graha_neo = selectedGrahaNeo;
    }

    router.post(
      '/checkout',
      {
        items: cartItems.map((item) => ({
          product_id: item.product_id,
          quantity: item.quantity,
        })),
        notes: notes,
        reward_selections: rewardSelections,
      },
      {
        onSuccess: () => {
          clearCart();
        },
        onFinish: () => {
          setIsSubmitting(false);
          setShowConfirmModal(false);
        },
      }
    );
  };

  const projectedRewards = preview?.projected || currentRewards;

  return (
    <GuestLayout pharmacy={pharmacy} milestone={milestone} title="Keranjang Belanja">
      <Head title="Keranjang Belanja & Review Reward" />

      {/* Cart Top Bar */}
      <div className="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
        <Link
          href="/katalog"
          className="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-900"
        >
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
            <h3 className="font-bold text-base text-slate-800 mb-1">
              Keranjang Belanja Kosong
            </h3>
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
            {/* Projected Milestone & Reward Banner */}
            <div className="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-4 rounded-2xl shadow-md relative overflow-hidden space-y-2">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2 text-xs font-bold text-emerald-200 uppercase tracking-wide">
                  <Sparkles className="w-4 h-4 text-amber-300" />
                  <span>Simulasi Perolehan Reward</span>
                </div>
                {isLoadingPreview && (
                  <span className="text-[10px] text-emerald-200 animate-pulse font-medium">
                    Menghitung bonus...
                  </span>
                )}
              </div>

              <p className="text-xs text-emerald-50 leading-relaxed">
                Akumulasi belanja Anda akan menjadi{' '}
                <strong className="text-amber-300 font-bold">
                  {formatRupiah(projectedTotal)}
                </strong>
                .
              </p>

              {/* Physical Gifts & Vouchers Preview */}
              {projectedRewards && (
                <div className="pt-2 border-t border-white/20 space-y-1.5 text-xs">
                  {projectedRewards.total_vouchers > 0 && (
                    <div className="inline-flex items-center gap-1.5 bg-amber-400/20 text-amber-200 px-2.5 py-1 rounded-lg font-bold border border-amber-300/30">
                      <span>🎟️</span>
                      <span>
                        Total Voucher Diraih:{' '}
                        {formatRupiah(projectedRewards.total_vouchers)}
                      </span>
                    </div>
                  )}

                  {Object.entries(projectedRewards.physical_rewards || {}).length > 0 && (
                    <div className="flex flex-wrap gap-1.5 pt-1">
                      {Object.entries(projectedRewards.physical_rewards).map(([giftName, qty]) => (
                        <span
                          key={giftName}
                          className="bg-white/15 backdrop-blur-md px-2.5 py-0.5 rounded-full text-[11px] font-semibold flex items-center gap-1 text-white border border-white/20"
                        >
                          <span>🎁</span>
                          <span>{giftName}: {qty} pcs</span>
                        </span>
                      ))}
                    </div>
                  )}

                  {projectedRewards.details?.exeltis?.program_note &&
                    (projectedRewards.details.exeltis.lanso_box > 0 ||
                      projectedRewards.details.exeltis.sucra_fls > 0) && (
                      <p className="text-[11px] text-amber-200 pt-1">
                        ✨ Exeltis: {projectedRewards.details.exeltis.lanso_box} Box Lansoprazole &amp;{' '}
                        {projectedRewards.details.exeltis.sucra_fls} Botol Sucralfate (Bebas Pilih Hadiah di Program).
                      </p>
                    )}
                </div>
              )}
            </div>

            {/* UNLOCKED CHOICE REWARDS SECTION: Interbat */}
            {hasInterbatChoice && (
              <div className="bg-amber-50/90 border-2 border-amber-300 rounded-2xl p-4 shadow-sm animate-in fade-in slide-in-from-top-2">
                <div className="flex items-center gap-2 mb-1.5">
                  <Gift className="w-5 h-5 text-amber-600 shrink-0" />
                  <h4 className="font-bold text-xs text-amber-900 uppercase tracking-wide">
                    🎉 Pilih Bonus Hadiah Interbat ({unlockedNow.interbat_choice_count} Hadiah)
                  </h4>
                </div>
                <p className="text-[11px] text-amber-800 mb-3 leading-relaxed">
                  Pesanan Anda memenuhi syarat Paket Interbat Generic (Kelipatan Rp 2.500.000). Silakan tentukan hadiah pilihan Anda:
                </p>
                <div className="grid grid-cols-1 gap-2">
                  {interbatChoices.map((opt) => (
                    <label
                      key={opt}
                      className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all ${
                        selectedInterbat === opt
                          ? 'bg-amber-100/90 border-amber-500 font-bold text-amber-950 shadow-xs'
                          : 'bg-white border-amber-200 text-slate-700 hover:bg-amber-50/50'
                      }`}
                    >
                      <div className="flex items-center gap-2.5">
                        <input
                          type="radio"
                          name="interbat_choice"
                          value={opt}
                          checked={selectedInterbat === opt}
                          onChange={() => setSelectedInterbat(opt)}
                          className="w-4 h-4 text-amber-600 focus:ring-amber-500"
                        />
                        <span className="text-xs">{opt}</span>
                      </div>
                      <span className="text-[10px] text-amber-700 font-semibold bg-white/80 px-2 py-0.5 rounded-full border border-amber-200">
                        Pilihan
                      </span>
                    </label>
                  ))}
                </div>
              </div>
            )}

            {/* UNLOCKED CHOICE REWARDS SECTION: Graha Neo Protifed */}
            {hasGrahaNeoChoice && (
              <div className="bg-blue-50/90 border-2 border-blue-300 rounded-2xl p-4 shadow-sm animate-in fade-in slide-in-from-top-2">
                <div className="flex items-center gap-2 mb-1.5">
                  <Gift className="w-5 h-5 text-blue-600 shrink-0" />
                  <h4 className="font-bold text-xs text-blue-900 uppercase tracking-wide">
                    🎉 Pilih Hadiah Neo Protifed Graha ({unlockedNow.graha_neo_choice_count} Hadiah)
                  </h4>
                </div>
                <p className="text-[11px] text-blue-800 mb-3 leading-relaxed">
                  Pesanan Neo Protifed Anda mencapai kelipatan 25 Box. Dapatkan Voucher Rp 50.000 + Hadiah Pilihan:
                </p>
                <div className="grid grid-cols-1 gap-2">
                  {grahaNeoChoices.map((opt) => (
                    <label
                      key={opt}
                      className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all ${
                        selectedGrahaNeo === opt
                          ? 'bg-blue-100/90 border-blue-500 font-bold text-blue-950 shadow-xs'
                          : 'bg-white border-blue-200 text-slate-700 hover:bg-blue-50/50'
                      }`}
                    >
                      <div className="flex items-center gap-2.5">
                        <input
                          type="radio"
                          name="graha_neo_choice"
                          value={opt}
                          checked={selectedGrahaNeo === opt}
                          onChange={() => setSelectedGrahaNeo(opt)}
                          className="w-4 h-4 text-blue-600 focus:ring-blue-500"
                        />
                        <span className="text-xs">{opt}</span>
                      </div>
                      <span className="text-[10px] text-blue-700 font-semibold bg-white/80 px-2 py-0.5 rounded-full border border-blue-200">
                        Pilihan
                      </span>
                    </label>
                  ))}
                </div>
              </div>
            )}

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
                    <QuantityStepper
                      value={item.quantity}
                      onChange={(newQty) => updateQuantity(item, newQty)}
                      min={1}
                      max={9999}
                      size="sm"
                    />

                    <button
                      type="button"
                      onClick={() => removeItem(item.product_id)}
                      className="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                      title="Hapus"
                      aria-label="Hapus item"
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
          <div className="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-200 text-center animate-in fade-in zoom-in duration-150">
            <div className="w-12 h-12 rounded-full bg-brand-100 text-brand-600 mx-auto flex items-center justify-center mb-3">
              <Send className="w-6 h-6" />
            </div>
            <h3 className="text-base font-bold text-slate-900 mb-1">
              Konfirmasi Pengiriman PO
            </h3>
            <p className="text-xs text-slate-500 mb-3 leading-relaxed">
              Anda akan mengirimkan pesanan senilai{' '}
              <strong className="text-slate-800">{formatRupiah(totalAmount)}</strong> untuk Apotek{' '}
              <strong>{pharmacy?.name}</strong>.
            </p>

            {/* Choice Summary in Confirmation */}
            {(hasInterbatChoice || hasGrahaNeoChoice) && (
              <div className="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-3 text-left space-y-1">
                <span className="text-[10px] font-bold text-slate-500 uppercase block">
                  Hadiah Pilihan Anda:
                </span>
                {hasInterbatChoice && (
                  <p className="text-xs font-semibold text-amber-900 flex items-center gap-1.5">
                    <span>🎁 Interbat:</span>
                    <span className="text-brand-700">{selectedInterbat}</span>
                  </p>
                )}
                {hasGrahaNeoChoice && (
                  <p className="text-xs font-semibold text-blue-900 flex items-center gap-1.5">
                    <span>🎁 Neo Protifed:</span>
                    <span className="text-brand-700">{selectedGrahaNeo}</span>
                  </p>
                )}
              </div>
            )}

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
