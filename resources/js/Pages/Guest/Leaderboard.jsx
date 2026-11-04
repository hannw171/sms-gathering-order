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
        <div className="bg-white rounded-2xl p-2.5 px-3.5 flex items-center justify-between border border-slate-200/90 shadow-xs">
          <div className="flex items-center gap-2">
            <span className="relative flex h-2.5 w-2.5">
              <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
              <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500" />
            </span>
            <span className="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">
              LIVE TRACKING WIB
            </span>
          </div>

          <div className="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
            <Clock className="w-3.5 h-3.5 text-brand-600" />
            <span>Sesi PO:</span>
            <span className="font-bold text-slate-900 font-mono">{countdown}</span>
          </div>
        </div>
      </div>

      {/* Grand Prize Announcement Banner (Figma node 1:2115) */}
      <div className="px-3.5 mb-4">
        <div className="bg-gradient-to-r from-brand-700 via-brand-600 to-brand-800 text-white rounded-2xl p-4 shadow-md relative overflow-hidden flex items-center gap-3.5">
          <div className="absolute top-0 right-0 -mr-6 -mt-6 w-32 h-32 bg-amber-400/20 rounded-full blur-2xl pointer-events-none" />

          <div className="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-amber-300 flex items-center justify-center shrink-0 shadow-sm relative z-10">
            <Gift className="w-5 h-5 text-amber-300" />
          </div>
          <div className="flex-1 min-w-0 relative z-10">
            <div className="inline-block bg-amber-400/20 text-amber-200 border border-amber-300/30 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider mb-1">
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
        <div className="flex items-center justify-between mb-2.5">
          <div className="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
            <Crown className="w-4 h-4 text-amber-500" />
            <span>TOP 3 MOST SPENDER GATHERING</span>
          </div>
          <span className="text-[10px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">
            Realtime
          </span>
        </div>

        <div className="grid grid-cols-3 gap-2 items-end pt-3">
          {/* Rank 2 (Silver) */}
          {top3[1] && (
            <div className="bg-white rounded-2xl p-2.5 border border-slate-200/90 text-center flex flex-col items-center shadow-xs order-1 relative">
              <div className="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-black text-xs mb-1.5 shadow-xs border border-slate-200">
                #2
              </div>
              <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                {top3[1].name}
              </h4>
              <span className="text-[10px] text-slate-400 block mb-1 truncate max-w-full">
                {top3[1].city || 'Jawa Tengah'}
              </span>
              <span className="font-bold text-[11px] text-slate-700 font-mono block">
                {formatRupiah(top3[1].total_spending)}
              </span>
            </div>
          )}

          {/* Rank 1 (Gold) */}
          {top3[0] && (
            <div className="bg-gradient-to-b from-amber-50/80 via-white to-white rounded-2xl p-3 border-2 border-amber-400 shadow-sm ring-4 ring-amber-400/10 text-center flex flex-col items-center order-2 relative -mt-3">
              <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-slate-950 flex items-center justify-center font-black text-sm mb-1.5 shadow-xs mt-0.5">
                #1
              </div>
              <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                {top3[0].name}
              </h4>
              <span className="text-[10px] text-amber-700 font-semibold block mb-1 truncate max-w-full">
                {top3[0].city || 'Jawa Tengah'}
              </span>
              <span className="font-black text-xs text-brand-700 font-mono block">
                {formatRupiah(top3[0].total_spending)}
              </span>
            </div>
          )}

          {/* Rank 3 (Bronze) */}
          {top3[2] && (
            <div className="bg-white rounded-2xl p-2.5 border border-slate-200/90 text-center flex flex-col items-center shadow-xs order-3 relative">
              <div className="w-8 h-8 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center font-black text-xs mb-1.5 shadow-xs border border-amber-200/80">
                #3
              </div>
              <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                {top3[2].name}
              </h4>
              <span className="text-[10px] text-slate-400 block mb-1 truncate max-w-full">
                {top3[2].city || 'Jawa Tengah'}
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
                className={`p-3 flex items-center justify-between gap-3 transition-colors ${
                  isMe ? 'bg-brand-50/70 border-l-4 border-l-brand-600' : 'hover:bg-slate-50/60'
                }`}
              >
                <div className="flex items-center gap-3 min-w-0">
                  <span
                    className={`w-7 h-7 rounded-xl font-bold text-xs flex items-center justify-center shrink-0 border ${
                      isMe
                        ? 'bg-brand-600 text-white border-brand-600 shadow-xs'
                        : 'bg-slate-100 text-slate-600 border-slate-200/80'
                    }`}
                  >
                    #{item.rank}
                  </span>
                  <div className="min-w-0">
                    <h5 className="font-semibold text-xs text-slate-900 truncate flex items-center">
                      <span className="truncate">{item.name}</span>
                      {isMe && (
                        <span className="ml-1.5 text-[9px] bg-brand-600 text-white font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wider shrink-0 shadow-2xs">
                          ANDA
                        </span>
                      )}
                    </h5>
                    <span className="text-[11px] text-slate-400 block truncate mt-0.5">
                      {item.city || 'Jawa Tengah'}
                    </span>
                  </div>
                </div>

                <span
                  className={`font-mono text-xs shrink-0 ${
                    isMe ? 'font-black text-brand-800' : 'font-bold text-brand-700'
                  }`}
                >
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
          <div className="bg-brand-900/95 backdrop-blur-md text-white p-3.5 px-4 rounded-2xl shadow-floating flex items-center justify-between border border-brand-700/50">
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-400 to-amber-300 text-slate-950 font-black text-xs flex items-center justify-center shadow-sm shrink-0">
                #{myRank}
              </div>
              <div className="min-w-0">
                <span className="text-[10px] text-brand-200 block uppercase font-bold tracking-wider">
                  Posisi Apotek Anda:
                </span>
                <span className="text-xs font-bold text-white truncate max-w-[170px] block">
                  {currentPharmacy.name}
                </span>
              </div>
            </div>

            <div className="text-right shrink-0">
              <span className="text-[10px] text-brand-200 block uppercase font-bold tracking-wider">
                Total Transaksi:
              </span>
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
