import React, { useState, useEffect, useMemo } from 'react';
import { Head, Link, router, usePoll } from '@inertiajs/react';
import axios from 'axios';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah } from '../../utils/format';
import { useCart } from '../../hooks/useCart';
import QuantityStepper from '../../Components/QuantityStepper';
import RewardSelectionModal from '../../Components/RewardSelectionModal';
import {
  Trash2,
  ArrowLeft,
  ArrowRight,
  Send,
  Sparkles,
  Gift,
  CheckCircle,
  ShoppingBag,
  HelpCircle,
  Clock,
  Lock,
} from 'lucide-react';

export default function Cart({
  pharmacy,
  milestone,
  currentRewards,
  rewardOptions,
  manufacturers = [],
  event,
  errors,
}) {
  // Auto-poll cart every 4 seconds to sync manufacturer session state in real-time
  usePoll(4000, {
    only: ['manufacturers', 'event'],
  });

  const { cartItems, totalItems, totalAmount, updateQuantity, removeItem, clearCart } = useCart();
  const [notes, setNotes] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [isRewardModalOpen, setIsRewardModalOpen] = useState(false);

  const closedCartItems = useMemo(() => {
    return cartItems.filter((item) => {
      const m = (manufacturers || []).find(
        (man) => man.id === item.manufacturer_id || man.name === item.manufacturer_name
      );
      return m && m.is_ordering_open === false;
    });
  }, [cartItems, manufacturers]);

  // Dynamic choice selections map: { [progKey]: selectedOption }
  const [selectedChoices, setSelectedChoices] = useState({
    graha_neo: rewardOptions?.graha_neo?.[0] || 'Magic Com Cosmos',
    interbat: rewardOptions?.interbat?.[0] || 'Elektrik Kettle',
    exeltis_lanso: '',
    exeltis_sucra: '',
  });

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

  // Build list of active unlocked choice programs
  const unlockedPrograms = useMemo(() => {
    const list = [];

    const grahaNeoCount = unlockedNow.graha_neo_choice_count || 0;
    if (grahaNeoCount > 0) {
      for (let i = 1; i <= grahaNeoCount; i++) {
        list.push({
          key: grahaNeoCount > 1 ? `graha_neo_${i}` : 'graha_neo',
          baseKey: 'graha_neo',
          slotIndex: i,
          slotTotal: grahaNeoCount,
          manufacturer: 'PT. GRAHA FARMA',
          title: grahaNeoCount > 1 ? `Neo Protifed Graha (Hadiah #${i})` : 'Neo Protifed Graha Farma',
          badge: grahaNeoCount > 1 ? `Kelipatan 25 Box #${i}` : 'Kelipatan 25 Box',
          description:
            'Pesanan Neo Protifed Anda mencapai kelipatan 25 Box. Dapatkan Voucher Rp 50.000 + Hadiah Pilihan:',
          options: unlockedNow.graha_neo_options || rewardOptions?.graha_neo || ['Magic Com Cosmos', 'Mixer Miyako'],
        });
      }
    }

    const interbatCount = unlockedNow.interbat_choice_count || 0;
    if (interbatCount > 0) {
      for (let i = 1; i <= interbatCount; i++) {
        list.push({
          key: interbatCount > 1 ? `interbat_${i}` : 'interbat',
          baseKey: 'interbat',
          slotIndex: i,
          slotTotal: interbatCount,
          manufacturer: 'INTERBAT',
          title: interbatCount > 1 ? `Interbat Generic (Hadiah #${i})` : 'Interbat Generic',
          badge: interbatCount > 1 ? `Paket Rp 2.5 Jt #${i}` : 'Paket Rp 2.500.000',
          description:
            'Pesanan Anda memenuhi syarat Paket Interbat Generic. Silakan tentukan hadiah pilihan Anda:',
          options:
            unlockedNow.interbat_options || rewardOptions?.interbat || ['Elektrik Kettle', 'Panci Kuah', 'Panci Mini Listrik'],
        });
      }
    }

    if (
      (unlockedNow.exeltis_lanso_choice_count || 0) > 0 &&
      (unlockedNow.exeltis_lanso_options?.length || 0) > 0
    ) {
      list.push({
        key: 'exeltis_lanso',
        baseKey: 'exeltis_lanso',
        slotIndex: 1,
        slotTotal: 1,
        manufacturer: 'EXELTIS',
        title: 'Lansoprazole (Exeltis)',
        badge: `${unlockedNow.exeltis_lanso_qty} Box`,
        description: `Pesanan Lansoprazole mencapai ${unlockedNow.exeltis_lanso_qty} Box. Bebas pilih hadiah dari tier yang Anda capai:`,
        options: unlockedNow.exeltis_lanso_options,
      });
    }

    if (
      (unlockedNow.exeltis_sucra_choice_count || 0) > 0 &&
      (unlockedNow.exeltis_sucra_options?.length || 0) > 0
    ) {
      list.push({
        key: 'exeltis_sucra',
        baseKey: 'exeltis_sucra',
        slotIndex: 1,
        slotTotal: 1,
        manufacturer: 'EXELTIS',
        title: 'Sucralfate (Exeltis)',
        badge: `${unlockedNow.exeltis_sucra_qty} Botol`,
        description: `Pesanan Sucralfate mencapai ${unlockedNow.exeltis_sucra_qty} Botol. Bebas pilih hadiah dari tier yang Anda capai:`,
        options: unlockedNow.exeltis_sucra_options,
      });
    }

    return list;
  }, [unlockedNow, rewardOptions]);

  // Synchronize smart default selections whenever unlocked programs change
  useEffect(() => {
    if (unlockedPrograms.length > 0) {
      setSelectedChoices((prev) => {
        let changed = false;
        const next = { ...prev };
        unlockedPrograms.forEach((prog) => {
          if (!next[prog.key] || !prog.options.includes(next[prog.key])) {
            next[prog.key] = prog.options[0];
            changed = true;
          }
        });
        return changed ? next : prev;
      });
    }
  }, [unlockedPrograms]);

  // Projected spending after this order
  const currentSpending = milestone?.total_spending || 0;
  const projectedTotal = currentSpending + totalAmount;

  const handleConfirmOrder = () => {
    setIsSubmitting(true);
    const rewardSelections = {};

    unlockedPrograms.forEach((prog) => {
      const chosen = selectedChoices[prog.key];
      if (chosen) {
        rewardSelections[prog.key] = chosen;
      }
    });

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
                    <div className="inline-flex flex-wrap items-center gap-1.5 bg-amber-400/20 text-amber-200 px-2.5 py-1 rounded-lg font-bold border border-amber-300/30">
                      <span>🎟️</span>
                      <span>
                        Total Voucher Diraih:{' '}
                        {formatRupiah(projectedRewards.total_vouchers)}
                      </span>
                      {preview?.previous?.total_vouchers > 0 && (
                        <span className="text-[10px] font-normal text-amber-100 bg-amber-500/30 border border-amber-300/30 px-1.5 py-0.5 rounded">
                          (+{formatRupiah(Math.max(0, (projectedRewards.total_vouchers || 0) - (preview.previous.total_vouchers || 0)))} dari PO ini)
                        </span>
                      )}
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

            {/* ========================================================================= */}
            {/* ADAPTIVE REWARD CHOICE SECTION                                            */}
            {/* Case 1: Exactly 1 reward program -> Inline on Cart Page                   */}
            {/* Case 2: Multiple reward programs (> 1) -> Summary card + Selection Modal   */}
            {/* ========================================================================= */}

            {unlockedPrograms.length === 1 && (
              <div className="bg-blue-50/90 border-2 border-blue-300 rounded-2xl p-4 shadow-sm animate-in fade-in slide-in-from-top-2">
                <div className="flex items-center justify-between gap-2 mb-1.5">
                  <div className="flex items-center gap-2">
                    <Gift className="w-5 h-5 text-blue-600 shrink-0" />
                    <h4 className="font-bold text-xs text-blue-900 uppercase tracking-wide">
                      🎉 PILIH HADIAH {unlockedPrograms[0].title.toUpperCase()}
                    </h4>
                  </div>
                  <span className="text-[10px] bg-blue-200/80 text-blue-800 font-bold px-2 py-0.5 rounded-full shrink-0">
                    {unlockedPrograms[0].badge}
                  </span>
                </div>
                <p className="text-[11px] text-blue-800 mb-3 leading-relaxed">
                  {unlockedPrograms[0].description}
                </p>
                <div className="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto pr-1">
                  {unlockedPrograms[0].options.map((opt, idx) => (
                    <label
                      key={opt}
                      className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all ${
                        selectedChoices[unlockedPrograms[0].key] === opt
                          ? 'bg-blue-100/90 border-blue-500 font-bold text-blue-950 shadow-xs'
                          : 'bg-white border-blue-200 text-slate-700 hover:bg-blue-50/50'
                      }`}
                    >
                      <div className="flex items-center gap-2.5 min-w-0 pr-2">
                        <input
                          type="radio"
                          name={`inline_choice_${unlockedPrograms[0].key}`}
                          value={opt}
                          checked={selectedChoices[unlockedPrograms[0].key] === opt}
                          onChange={() =>
                            setSelectedChoices((prev) => ({
                              ...prev,
                              [unlockedPrograms[0].key]: opt,
                            }))
                          }
                          className="w-4 h-4 text-blue-600 focus:ring-blue-500"
                        />
                        <span className="text-xs truncate">{opt}</span>
                      </div>
                      {idx === 0 && unlockedPrograms[0].options.length > 1 ? (
                        <span className="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 shrink-0">
                          Tier Tertinggi
                        </span>
                      ) : (
                        <span className="text-[10px] text-blue-700 font-semibold bg-white/80 px-2 py-0.5 rounded-full border border-blue-200 shrink-0">
                          Pilihan
                        </span>
                      )}
                    </label>
                  ))}
                </div>
              </div>
            )}

            {unlockedPrograms.length > 1 && (
              <div className="bg-gradient-to-br from-indigo-50 via-blue-50 to-white border-2 border-indigo-200 rounded-2xl p-4 shadow-sm animate-in fade-in slide-in-from-top-2">
                <div className="flex items-start justify-between gap-3">
                  <div className="flex items-center gap-2.5">
                    <div className="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                      <Gift className="w-5 h-5" />
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <h4 className="text-[13px] font-bold text-indigo-950 uppercase tracking-wide">
                          🎉 Anda Berhak Memilih {unlockedPrograms.length} Hadiah!
                        </h4>
                      </div>
                      <p className="text-[11px] text-indigo-800/90 mt-0.5">
                        Terbuka dari: {unlockedPrograms.map((p) => p.title).join(', ')}
                      </p>
                    </div>
                  </div>
                </div>

                {/* Current Selection List */}
                <div className="mt-3 pt-3 border-t border-indigo-100/90 space-y-1.5">
                  {unlockedPrograms.map((prog) => (
                    <div
                      key={prog.key}
                      className="flex items-center justify-between bg-white/90 rounded-xl px-3 py-2 text-[12px] border border-indigo-100 shadow-2xs"
                    >
                      <span className="font-semibold text-slate-700 text-[11px] truncate mr-2">
                        {prog.title}:
                      </span>
                      <span className="font-bold text-indigo-700 truncate text-[11px] text-right">
                        {selectedChoices[prog.key] || 'Belum dipilih'}
                      </span>
                    </div>
                  ))}
                </div>

                <button
                  type="button"
                  onClick={() => setIsRewardModalOpen(true)}
                  className="w-full mt-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[13px] py-2.5 px-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]"
                >
                  <Sparkles className="w-4 h-4 text-amber-300" />
                  <span>Buka &amp; Tentukan Pilihan Hadiah ({unlockedPrograms.length} Hadiah)</span>
                  <ArrowRight className="w-4 h-4" />
                </button>
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
                      min={item.min_order || 1}
                      max={9999}
                      step={item.step || 1}
                      minOrder={item.min_order || 1}
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

            {/* Manufacturer Specific Closed Notice */}
            {closedCartItems.length > 0 && (
              <div className="bg-rose-50 border border-rose-300 rounded-2xl p-3.5 flex items-start gap-2.5 text-xs text-rose-900 shadow-xs">
                <Lock className="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
                <div>
                  <strong className="block font-bold">Produk Sesi Ditutup di Keranjang</strong>
                  <span className="text-[11px] text-rose-800 leading-snug">
                    Terdapat {closedCartItems.length} produk dari pabrikan yang sesinya sedang ditutup oleh panitia ({closedCartItems.map((c) => c.name).join(', ')}). Harap hapus item tersebut untuk melanjutkan checkout.
                  </span>
                </div>
              </div>
            )}

            {/* Ordering Closed / Scheduled Notice Banner (Only when ALL manufacturers are closed) */}
            {event?.is_ordering_open === false && (manufacturers || []).every((m) => m.is_ordering_open === false) && (
              <div className="bg-amber-50 border border-amber-300 rounded-2xl p-3.5 flex items-start gap-2.5 text-xs text-amber-900 shadow-xs">
                <Clock className="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
                <div>
                  <strong className="block font-bold">
                    {event?.ordering_status === 'scheduled'
                      ? 'Sesi Pemesanan Belum Dimulai'
                      : event?.ordering_status === 'ended'
                      ? 'Sesi Pemesanan Telah Berakhir'
                      : 'Sesi Pemesanan Ditutup Sementara'}
                  </strong>
                  <span className="text-[11px] text-amber-800 leading-snug">
                    {event?.ordering_status === 'scheduled'
                      ? 'Anda dapat memilih obat dan menyiapkan keranjang terlebih dahulu. Tombol checkout akan aktif otomatis saat sesi dimulai.'
                      : event?.ordering_status === 'ended'
                      ? 'Waktu sesi pemesanan gathering telah selesai. Pesanan baru tidak dapat dikirim.'
                      : 'Panitia sedang menjeda penerimaan pesanan. Silakan tunggu informasi berikutnya dari panitia.'}
                  </span>
                </div>
              </div>
            )}

            {/* Submit CTA Button */}
            <button
              type="button"
              onClick={() => setShowConfirmModal(true)}
              disabled={isSubmitting || totalItems === 0 || closedCartItems.length > 0}
              className="w-full bg-brand-600 hover:bg-brand-700 active:bg-brand-800 disabled:opacity-50 text-white font-bold py-3.5 px-4 rounded-2xl shadow-floating flex items-center justify-center gap-2 text-sm transition-all"
            >
              {closedCartItems.length > 0 ? (
                <>
                  <Lock className="w-4 h-4" />
                  <span>Ada Produk Sesi Ditutup ({closedCartItems.length})</span>
                </>
              ) : (
                <>
                  <Send className="w-4 h-4" />
                  <span>Kirim Pesanan Sekarang ({formatRupiah(totalAmount)})</span>
                </>
              )}
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
            {unlockedPrograms.length > 0 && (
              <div className="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-3 text-left space-y-1.5">
                <span className="text-[10px] font-bold text-slate-500 uppercase block tracking-wider">
                  Hadiah Pilihan Anda:
                </span>
                {unlockedPrograms.map((prog) => (
                  <div
                    key={prog.key}
                    className="text-xs font-semibold text-slate-800 flex items-center justify-between gap-1.5"
                  >
                    <span className="text-slate-600 truncate mr-2">🎁 {prog.title}:</span>
                    <span className="text-brand-700 font-bold truncate max-w-[170px] text-right">
                      {selectedChoices[prog.key]}
                    </span>
                  </div>
                ))}
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

      {/* Reward Selection Modal / Drawer for Multi-Program Choices */}
      <RewardSelectionModal
        isOpen={isRewardModalOpen}
        onClose={() => setIsRewardModalOpen(false)}
        unlockedPrograms={unlockedPrograms}
        selectedChoices={selectedChoices}
        onSelectChoice={(key, value) => {
          setSelectedChoices((prev) => ({
            ...prev,
            [key]: value,
          }));
        }}
      />
    </GuestLayout>
  );
}
