import React, { useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah } from '../../utils/format';
import { useCart } from '../../hooks/useCart';
import { CheckCircle2, Trophy, ArrowRight, ShoppingBag, Sparkles, Gift, Ticket, Award } from 'lucide-react';

export default function Success({ order, milestone, pharmacy, rewards }) {
  const { clearCart } = useCart(pharmacy?.id);

  useEffect(() => {
    clearCart();
  }, [clearCart]);

  const hasVouchers = (rewards?.vouchers || 0) > 0;
  const physicalRewards = rewards?.physical_rewards || [];
  const choiceSelections = order?.reward_selections || rewards?.choice_selections || [];
  const customNotes = rewards?.custom_notes || [];
  const voucherBreakdown = rewards?.voucher_breakdown || {};
  const hasAnyReward = hasVouchers || physicalRewards.length > 0 || choiceSelections.length > 0 || customNotes.length > 0;

  return (
    <GuestLayout pharmacy={pharmacy} milestone={milestone} title="Pesanan Berhasil">
      <Head title="Pesanan Berhasil & Hadiah Gathering" />

      <div className="p-4 flex flex-col items-center text-center">
        {/* Animated Celebration Icon */}
        <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center my-4 shadow-md ring-8 ring-emerald-50">
          <CheckCircle2 className="w-10 h-10 stroke-[2.5]" />
        </div>

        <span className="text-[11px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 border border-emerald-200/80 px-3 py-0.5 rounded-full mb-1">
          ORDER TERVERIFIKASI
        </span>
        <h1 className="text-xl font-bold text-slate-900 tracking-tight mb-1">
          Pesanan Berhasil Dikirim!
        </h1>
        <p className="text-xs text-slate-500 max-w-xs mb-4">
          Nomor PO Anda: <strong className="text-brand-700 font-mono text-sm">{order.order_number}</strong>
        </p>

        {/* Milestone & Rank Update Card */}
        <div className="w-full bg-gradient-to-br from-brand-700 via-brand-600 to-brand-900 text-white rounded-2xl p-4 shadow-md relative overflow-hidden mb-4 text-left">
          <div className="absolute top-0 right-0 -mr-6 -mt-6 w-32 h-32 bg-amber-400/15 rounded-full blur-2xl pointer-events-none" />

          <div className="flex items-center justify-between mb-2">
            <span className="text-[10px] font-bold text-amber-300 uppercase tracking-wider flex items-center gap-1">
              <Sparkles className="w-3.5 h-3.5" />
              <span>UPDATE PERINGKAT GATHERING</span>
            </span>
            <div className="bg-amber-400/20 text-amber-300 border border-amber-300/30 text-[10px] font-bold px-2 py-0.5 rounded-full">
              LIVE
            </div>
          </div>

          <div className="flex items-center justify-between">
            <div>
              <span className="text-xs text-brand-100 block">Posisi Apotek Anda:</span>
              <span className="text-2xl font-black text-amber-300 flex items-center gap-1.5">
                <span>Rank #{milestone?.current_rank || '-'}</span>
              </span>
            </div>
            <div className="text-right">
              <span className="text-xs text-brand-100 block">Total Belanja Akumulasi:</span>
              <span className="text-base font-bold text-white">
                {formatRupiah(milestone?.total_spending || 0)}
              </span>
            </div>
          </div>
        </div>

        {/* Hadiah & Voucher Gathering Card */}
        {hasAnyReward && (
          <div className="w-full bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-300 rounded-2xl p-4 shadow-sm mb-4 text-left">
            <div className="flex items-center gap-2 mb-3 pb-2 border-b border-amber-200/80">
              <div className="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                <Gift className="w-4 h-4" />
              </div>
              <div>
                <h3 className="text-xs font-bold text-amber-950 uppercase tracking-wider">
                  Hadiah & Voucher Diperoleh
                </h3>
                <span className="text-[10px] text-amber-800">
                  Akumulasi reward pabrikan dari pesanan Anda
                </span>
              </div>
            </div>

            {/* Total Voucher */}
            {hasVouchers && (
              <div className="bg-white rounded-xl p-3 border border-amber-200/90 mb-2.5 flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Ticket className="w-4 h-4 text-emerald-600" />
                  <span className="text-xs font-semibold text-slate-700">Total Voucher Belanja:</span>
                </div>
                <span className="text-sm font-black text-emerald-700">
                  {formatRupiah(rewards.vouchers)}
                </span>
              </div>
            )}

            {/* Voucher breakdown */}
            {Object.keys(voucherBreakdown).length > 0 && (
              <div className="text-[11px] text-slate-600 mb-2.5 pl-1 space-y-1">
                <span className="font-semibold text-slate-800 block text-[10px] uppercase tracking-wide">
                  Rincian Voucher Pabrikan:
                </span>
                {Object.entries(voucherBreakdown).map(([slug, nominal]) => (
                  <div key={slug} className="flex justify-between items-center bg-white/70 px-2.5 py-1 rounded-lg border border-amber-100">
                    <span className="capitalize font-medium text-slate-700">{slug.replace(/-/g, ' ')}</span>
                    <span className="font-bold text-emerald-700">{formatRupiah(nominal)}</span>
                  </div>
                ))}
              </div>
            )}

            {/* Physical rewards list */}
            {physicalRewards.length > 0 && (
              <div className="mb-2.5 space-y-1">
                <span className="font-semibold text-slate-800 block text-[10px] uppercase tracking-wide pl-1">
                  Hadiah Langsung:
                </span>
                <div className="space-y-1">
                  {physicalRewards.map((reward, idx) => (
                    <div key={idx} className="flex items-center gap-2 bg-white px-2.5 py-1.5 rounded-lg border border-amber-200 text-xs text-amber-900 font-semibold shadow-2xs">
                      <Award className="w-3.5 h-3.5 text-amber-600 flex-shrink-0" />
                      <span>{reward}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Choice reward selections */}
            {choiceSelections.length > 0 && (
              <div className="mb-2.5 space-y-1">
                <span className="font-semibold text-slate-800 block text-[10px] uppercase tracking-wide pl-1">
                  Hadiah Pilihan Anda:
                </span>
                <div className="space-y-1">
                  {choiceSelections.map((choice, idx) => (
                    <div key={idx} className="flex items-center justify-between bg-white px-2.5 py-1.5 rounded-lg border border-amber-300 text-xs font-bold text-amber-950 shadow-2xs">
                      <span className="capitalize text-[11px] text-slate-600 font-medium">
                        {choice.manufacturer_slug?.replace(/-/g, ' ') || 'Pilihan'}:
                      </span>
                      <span className="text-amber-800">{choice.selected_option}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Custom program notes (e.g. Exeltis) */}
            {customNotes.length > 0 && (
              <div className="mt-2 space-y-1">
                {customNotes.map((note, idx) => (
                  <div key={idx} className="p-2 rounded-lg bg-amber-100/70 border border-amber-200/90 text-[11px] text-amber-900 leading-tight">
                    <strong className="block font-bold text-[10px] uppercase tracking-wide text-amber-800">{note.manufacturer}:</strong>
                    <span>{note.note}</span>
                  </div>
                ))}
              </div>
            )}

            <p className="mt-3 text-[10px] text-amber-800/80 italic text-center">
              * Hadiah fisik & voucher dapat diambil di meja panitia pada akhir sesi gathering.
            </p>
          </div>
        )}

        {/* Order Items Breakdown */}
        <div className="w-full bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 text-left mb-6 space-y-2">
          <h4 className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">
            Rincian Pesanan
          </h4>
          <div className="divide-y divide-slate-100 max-h-48 overflow-y-auto pr-1">
            {order.items?.map((item) => (
              <div key={item.id} className="py-2 flex justify-between text-xs">
                <div>
                  <span className="font-semibold text-slate-900 block">{item.product_name}</span>
                  <span className="text-[11px] text-slate-500">{item.quantity} &times; {formatRupiah(item.price)}</span>
                </div>
                <span className="font-bold text-brand-700">{formatRupiah(item.subtotal)}</span>
              </div>
            ))}
          </div>

          <div className="pt-2 border-t border-slate-100 flex justify-between items-baseline font-bold text-xs">
            <span className="text-slate-900">Total Nominal Transaksi</span>
            <span className="text-base text-brand-700">{formatRupiah(order.total_amount)}</span>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="w-full flex flex-col gap-2.5">
          <Link
            href="/katalog"
            className="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 rounded-xl shadow-md flex items-center justify-center gap-2 text-xs transition-colors"
          >
            <ShoppingBag className="w-4 h-4" />
            <span>Tambah Pesanan Lain di Katalog</span>
          </Link>
          <Link
            href="/leaderboard"
            className="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 text-xs transition-colors"
          >
            <Trophy className="w-4 h-4 text-amber-600" />
            <span>Lihat Posisi di Papan Peringkat</span>
          </Link>
        </div>
      </div>
    </GuestLayout>
  );
}
