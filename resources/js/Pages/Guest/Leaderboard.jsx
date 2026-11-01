import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah, formatCountdown } from '../../utils/format';
import { Trophy, Clock, Gift, Crown, Medal, ArrowUpRight, Sparkles } from 'lucide-react';

export default function Leaderboard({ leaderboard, currentPharmacy, currentMilestone, event }) {
  const [countdown, setCountdown] = useState(() => formatCountdown(event?.session_end_at));

  useEffect(() => {
    const timer = setInterval(() => {
      setCountdown(formatCountdown(event?.session_end_at));
    }, 1000);
    return () => clearInterval(timer);
  }, [event?.session_end_at]);

  const top3 = leaderboard.slice(0, 3);
  const remainingRanks = leaderboard.slice(3, 10);

  // Current logged in user rank
  const myRank = currentMilestone?.current_rank;

  return (
    <GuestLayout pharmacy={currentPharmacy} milestone={currentMilestone} title="Papan Peringkat">
      <Head title="Papan Peringkat Most Spender" />

      {/* Live Status & Sesi Timer Strip (Figma node 1:2102) */}
      <div className="p-3.5 pb-2">
        <div className="bg-slate-100/90 rounded-2xl p-2.5 px-3.5 flex items-center justify-between border border-slate-200/80 shadow-xs">
          <div className="flex items-center gap-2">
            <span className="relative flex h-2.5 w-2.5">
              <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
              <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500" />
            </span>
            <span className="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">
              LIVE TRACKING WIB
            </span>
          </div>

          <div className="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
            <Clock className="w-3.5 h-3.5 text-slate-400" />
            <span>Sesi PO:</span>
            <span className="font-bold text-slate-900 font-mono">{countdown}</span>
          </div>
        </div>
      </div>

      {/* Grand Prize Announcement Banner (Figma node 1:2115) */}
      <div className="px-3.5 mb-4">
        <div className="bg-gradient-to-r from-brand-700 via-brand-800 to-slate-950 text-white rounded-2xl p-3.5 shadow-md relative overflow-hidden flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center shrink-0 font-bold shadow-md">
            <Gift className="w-5 h-5 text-slate-900" />
          </div>
          <div className="flex-1 min-w-0">
            <div className="inline-block bg-amber-400/20 text-amber-300 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider mb-0.5">
              DOORPRIZE EXCLUSIVE
            </div>
            <p className="text-xs font-semibold text-white truncate">
              {event?.doorprize_announcement || 'Grand Prize Emas & Paket Liburan'}
            </p>
          </div>
        </div>
      </div>

      {/* Top 3 Podium (Figma node 1:2098) */}
      <div className="px-3.5 mb-5">
        <div className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
          <Crown className="w-4 h-4 text-amber-500" />
          <span>TOP 3 MOST SPENDER GATHERING</span>
        </div>

        <div className="grid grid-cols-3 gap-2 items-end pt-3">
          {/* Rank 2 (Silver) */}
          {top3[1] && (
            <div className="bg-white rounded-2xl p-2.5 border border-slate-200/90 text-center flex flex-col items-center shadow-xs order-1 relative">
              <div className="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-black text-xs mb-1.5 shadow-sm border border-slate-300">
                #2
              </div>
              <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                {top3[1].name}
              </h4>
              <span className="text-[10px] text-slate-400 block mb-1">
                {top3[1].city || top3[1].table_number}
              </span>
              <span className="font-bold text-[11px] text-slate-700 font-mono block">
                {formatRupiah(top3[1].total_spending)}
              </span>
            </div>
          )}

          {/* Rank 1 (Gold) */}
          {top3[0] && (
            <div className="bg-gradient-to-b from-amber-50 to-white rounded-2xl p-3 border-2 border-amber-400/80 text-center flex flex-col items-center shadow-md order-2 relative -mt-3 ring-4 ring-amber-100/50">
              <div className="absolute -top-3 w-6 h-6 rounded-full bg-amber-400 text-slate-900 flex items-center justify-center shadow-sm">
                👑
              </div>
              <div className="w-10 h-10 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center font-black text-sm mb-1.5 shadow-sm mt-1">
                #1
              </div>
              <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                {top3[0].name}
              </h4>
              <span className="text-[10px] text-amber-700 font-semibold block mb-1">
                {top3[0].city || top3[0].table_number}
              </span>
              <span className="font-black text-xs text-brand-700 font-mono block">
                {formatRupiah(top3[0].total_spending)}
              </span>
            </div>
          )}

          {/* Rank 3 (Bronze) */}
          {top3[2] && (
            <div className="bg-white rounded-2xl p-2.5 border border-slate-200/90 text-center flex flex-col items-center shadow-xs order-3 relative">
              <div className="w-8 h-8 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xs mb-1.5 shadow-sm border border-amber-200">
                #3
              </div>
              <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                {top3[2].name}
              </h4>
              <span className="text-[10px] text-slate-400 block mb-1">
                {top3[2].city || top3[2].table_number}
              </span>
              <span className="font-bold text-[11px] text-slate-700 font-mono block">
                {formatRupiah(top3[2].total_spending)}
              </span>
            </div>
          )}
        </div>
      </div>

      {/* Rank 4 - 10 List (Figma node 1:2098) */}
      <div className="px-3.5 pb-32">
        <div className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2.5">
          PERINGKAT 4 — 10
        </div>

        <div className="bg-white rounded-2xl border border-slate-200/90 shadow-xs divide-y divide-slate-100 overflow-hidden">
          {remainingRanks.map((item) => {
            const isMe = currentPharmacy?.id === item.id;

            return (
              <div
                key={item.id}
                className={`p-3 flex items-center justify-between gap-3 ${
                  isMe ? 'bg-amber-50/70 font-semibold' : ''
                }`}
              >
                <div className="flex items-center gap-3 min-w-0">
                  <span className="w-6 text-center font-bold text-xs text-slate-400">
                    #{item.rank}
                  </span>
                  <div className="min-w-0">
                    <h5 className="font-semibold text-xs text-slate-900 truncate">
                      {item.name}
                      {isMe && (
                        <span className="ml-1.5 text-[10px] bg-brand-600 text-white font-bold px-1.5 py-0.2 rounded">
                          ANDA
                        </span>
                      )}
                    </h5>
                    <span className="text-[11px] text-slate-400 block">
                      {item.city ? `${item.city} &bullet; ` : ''} {item.table_number || item.customer_code}
                    </span>
                  </div>
                </div>

                <span className="font-bold text-xs text-brand-700 shrink-0 font-mono">
                  {formatRupiah(item.total_spending)}
                </span>
              </div>
            );
          })}
        </div>
      </div>

      {/* Sticky Bottom My Rank Highlight */}
      {currentPharmacy && myRank && (
        <div className="fixed sm:absolute bottom-16 left-0 right-0 max-w-guest mx-auto px-4 z-40">
          <div className="bg-brand-900 text-white p-3 rounded-2xl shadow-floating flex items-center justify-between border border-brand-700/60">
            <div className="flex items-center gap-2.5">
              <div className="w-8 h-8 rounded-xl bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center">
                #{myRank}
              </div>
              <div>
                <span className="text-[10px] text-brand-200 block uppercase font-bold">Posisi Apotek Anda:</span>
                <span className="text-xs font-bold text-white truncate max-w-[160px] block">
                  {currentPharmacy.name}
                </span>
              </div>
            </div>

            <div className="text-right">
              <span className="text-[10px] text-brand-200 block">Total Transaksi:</span>
              <span className="text-xs font-bold text-amber-300 font-mono">
                {formatRupiah(currentMilestone?.total_spending || 0)}
              </span>
            </div>
          </div>
        </div>
      )}
    </GuestLayout>
  );
}
