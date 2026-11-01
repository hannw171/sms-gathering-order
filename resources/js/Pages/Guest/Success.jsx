import React from 'react';
import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah } from '../../utils/format';
import { CheckCircle2, Trophy, ArrowRight, ShoppingBag, Sparkles } from 'lucide-react';

export default function Success({ order, milestone, pharmacy }) {
  const unlockedTier = order?.unlocked_reward_tier || milestone?.unlocked_tier;

  return (
    <GuestLayout pharmacy={pharmacy} milestone={milestone} title="Pesanan Berhasil">
      <Head title="Pesanan Berhasil & Rank Milestone" />

      <div className="p-4 flex flex-col items-center text-center">
        {/* Animated Celebration Icon (Figma node 1:1330) */}
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

        {/* Milestone & Rank Update Card (Figma node 1:1330) */}
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

          {unlockedTier && (
            <div className="mt-3 pt-3 border-t border-white/15 flex items-center gap-2">
              <span className="text-xl">{unlockedTier.badge_icon || '🏆'}</span>
              <div>
                <span className="text-[10px] text-amber-200 block uppercase font-bold">Reward Berhasil Dibuka:</span>
                <span className="text-xs font-bold text-white">{unlockedTier.reward_description}</span>
              </div>
            </div>
          )}
        </div>

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
