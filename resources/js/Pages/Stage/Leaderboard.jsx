import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { formatRupiah, formatCountdown } from '../../utils/format';
import { Trophy, Clock, Flame, Crown, Sparkles, TrendingUp } from 'lucide-react';

export default function StageLeaderboard({ leaderboard: initialLeaderboard, stats: initialStats, event: initialEvent }) {
  const [leaderboard, setLeaderboard] = useState(initialLeaderboard || []);
  const [stats, setStats] = useState(initialStats || { total_gmv: 0, total_orders: 0 });
  const [event, setEvent] = useState(initialEvent || {});
  const [countdown, setCountdown] = useState(() => formatCountdown(initialEvent?.session_end_at));

  // Countdown timer effect
  useEffect(() => {
    const timer = setInterval(() => {
      setCountdown(formatCountdown(event?.session_end_at));
    }, 1000);
    return () => clearInterval(timer);
  }, [event?.session_end_at]);

  // Real-time polling every 5 seconds for stage display
  useEffect(() => {
    const poller = setInterval(async () => {
      try {
        const res = await fetch('/api/stage/data');
        if (res.ok) {
          const data = await res.json();
          setLeaderboard(data.leaderboard);
          setStats(data.stats);
          setEvent(data.event);
        }
      } catch (e) {
        console.error('Stage data fetch error', e);
      }
    }, 5000);

    return () => clearInterval(poller);
  }, []);

  const top1 = leaderboard[0];
  const top2 = leaderboard[1];
  const top3 = leaderboard[2];
  const rank4to10 = leaderboard.slice(3, 10);

  return (
    <div
      data-theme="stage-display"
      className="min-h-screen bg-[#090D16] text-slate-100 flex flex-col justify-between p-6 lg:p-10 select-none overflow-hidden relative font-sans"
    >
      <Head title="Live Stage Leaderboard - Big Screen" />

      {/* Ambient Radial Lights in Background */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[350px] bg-brand-600/15 rounded-full blur-[140px] pointer-events-none" />
      <div className="absolute bottom-10 right-10 w-[500px] h-[300px] bg-amber-500/10 rounded-full blur-[120px] pointer-events-none" />

      {/* Top Stage Header (Figma node 1:1435) */}
      <header className="relative z-10 flex items-center justify-between border-b border-white/10 pb-6">
        <div className="flex items-center gap-4">
          <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-slate-950 shadow-stage-gold">
            <Trophy className="w-8 h-8 stroke-[2.5]" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <span className="bg-amber-400/20 text-amber-300 border border-amber-400/30 text-xs font-black px-2.5 py-0.5 rounded-full tracking-widest uppercase">
                LIVE GATHERING 2026
              </span>
              <span className="text-xs text-slate-400 font-mono tracking-wider">
                SMS MEDIKA SURABAYA
              </span>
            </div>
            <h1 className="text-3xl lg:text-4xl font-black text-white tracking-tight mt-1">
              MOST SPENDER LEADERBOARD
            </h1>
          </div>
        </div>

        {/* Live Countdown & Total GMV Metrics */}
        <div className="flex items-center gap-6">
          <div className="bg-slate-800/80 backdrop-blur-md border border-white/10 rounded-2xl px-5 py-3 text-right shadow-lg">
            <span className="text-xs text-slate-400 block font-medium uppercase tracking-wider">
              Total Akumulasi PO Malam Ini
            </span>
            <span className="text-2xl font-black text-emerald-400 font-mono">
              {formatRupiah(stats.total_gmv)}
            </span>
          </div>

          <div className="bg-gradient-to-r from-brand-900 to-brand-800 border border-brand-500/30 rounded-2xl px-6 py-3 text-center shadow-lg">
            <span className="text-xs text-brand-200 block font-bold tracking-widest uppercase flex items-center justify-center gap-1.5">
              <Clock className="w-4 h-4 text-amber-400" />
              <span>Sesi PO Berakhir</span>
            </span>
            <span className="text-3xl font-black text-amber-300 font-mono tracking-wider">
              {countdown}
            </span>
          </div>
        </div>
      </header>

      {/* Main Center Stage Area: Top 3 Podium & Ranks 4-10 */}
      <main className="relative z-10 grid grid-cols-12 gap-8 my-auto py-6 items-center">
        
        {/* Left Side: Top 3 Grand Podium (7 cols) */}
        <div className="col-span-12 lg:col-span-7 flex flex-col justify-center">
          <div className="text-center mb-6">
            <span className="text-xs font-bold uppercase tracking-widest text-amber-400 flex items-center justify-center gap-2">
              <Sparkles className="w-4 h-4" />
              <span>PODIUM JUARA UTAMA</span>
              <Sparkles className="w-4 h-4" />
            </span>
          </div>

          <div className="grid grid-cols-3 gap-4 items-end">
            {/* Podium Rank 2 (Silver) */}
            <div className="flex flex-col items-center">
              <div className="w-16 h-16 rounded-full bg-slate-700 border-2 border-slate-300 flex items-center justify-center text-slate-100 font-black text-2xl shadow-lg mb-3">
                #2
              </div>
              <div className="w-full bg-slate-800/80 backdrop-blur-md border border-slate-400/30 rounded-2xl p-4 text-center shadow-xl h-64 flex flex-col justify-between">
                <div>
                  <span className="text-xs text-slate-400 block uppercase font-bold tracking-wider">
                    {top2?.city || 'Apotek'}
                  </span>
                  <h3 className="text-lg font-bold text-white line-clamp-2 mt-1">
                    {top2?.name || 'Menunggu Order...'}
                  </h3>
                  <span className="text-xs text-slate-400 block mt-1 font-mono">
                    {top2?.table_number}
                  </span>
                </div>
                <div className="pt-3 border-t border-white/10">
                  <span className="text-xs text-slate-400 block">Total Transaksi:</span>
                  <span className="text-xl font-black text-slate-200 font-mono">
                    {top2 ? formatRupiah(top2.total_spending) : '-'}
                  </span>
                </div>
              </div>
            </div>

            {/* Podium Rank 1 (Gold - Center & Elevated) */}
            <div className="flex flex-col items-center -mt-8">
              <div className="relative mb-3">
                <Crown className="w-10 h-10 text-amber-400 animate-bounce mx-auto" />
                <div className="w-20 h-20 rounded-full bg-gradient-to-tr from-amber-500 to-amber-300 border-4 border-amber-200 flex items-center justify-center text-slate-950 font-black text-3xl shadow-stage-gold">
                  #1
                </div>
              </div>
              <div className="w-full bg-gradient-to-b from-slate-800/95 to-slate-900 border-2 border-amber-400/70 rounded-3xl p-5 text-center shadow-2xl h-80 flex flex-col justify-between ring-8 ring-amber-400/10">
                <div>
                  <span className="bg-amber-400/20 text-amber-300 border border-amber-400/40 text-[11px] font-black px-3 py-1 rounded-full uppercase tracking-widest inline-block mb-2">
                    👑 GRAND CHAMPION
                  </span>
                  <h2 className="text-2xl font-black text-white line-clamp-2 leading-tight">
                    {top1?.name || 'Menunggu Order...'}
                  </h2>
                  <span className="text-sm text-amber-300 font-bold block mt-1">
                    {top1?.city} &bull; {top1?.table_number}
                  </span>
                </div>
                <div className="pt-4 border-t border-amber-400/20">
                  <span className="text-xs text-slate-400 uppercase font-bold tracking-wider block">Total Akumulasi:</span>
                  <span className="text-3xl font-black text-amber-400 font-mono tracking-tight">
                    {top1 ? formatRupiah(top1.total_spending) : '-'}
                  </span>
                </div>
              </div>
            </div>

            {/* Podium Rank 3 (Bronze) */}
            <div className="flex flex-col items-center">
              <div className="w-16 h-16 rounded-full bg-amber-900/80 border-2 border-amber-600 flex items-center justify-center text-amber-200 font-black text-2xl shadow-lg mb-3">
                #3
              </div>
              <div className="w-full bg-slate-800/80 backdrop-blur-md border border-amber-600/30 rounded-2xl p-4 text-center shadow-xl h-56 flex flex-col justify-between">
                <div>
                  <span className="text-xs text-amber-500 block uppercase font-bold tracking-wider">
                    {top3?.city || 'Apotek'}
                  </span>
                  <h3 className="text-lg font-bold text-white line-clamp-2 mt-1">
                    {top3?.name || 'Menunggu Order...'}
                  </h3>
                  <span className="text-xs text-slate-400 block mt-1 font-mono">
                    {top3?.table_number}
                  </span>
                </div>
                <div className="pt-3 border-t border-white/10">
                  <span className="text-xs text-slate-400 block">Total Transaksi:</span>
                  <span className="text-xl font-black text-amber-400 font-mono">
                    {top3 ? formatRupiah(top3.total_spending) : '-'}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Right Side: Rank 4 - 10 Two-Column Table (5 cols) */}
        <div className="col-span-12 lg:col-span-5 bg-slate-800/60 backdrop-blur-md border border-white/10 rounded-3xl p-6 shadow-2xl">
          <div className="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
            <span className="text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
              <TrendingUp className="w-4 h-4 text-emerald-400" />
              <span>PERINGKAT 4 SAMPAI 10</span>
            </span>
            <span className="text-xs text-slate-400">Update Tiap 5 Detik</span>
          </div>

          <div className="space-y-2.5">
            {rank4to10.length === 0 ? (
              <p className="text-sm text-slate-400 text-center py-8">Belum ada transaksi di peringkat 4–10.</p>
            ) : (
              rank4to10.map((item) => (
                <div
                  key={item.id}
                  className="bg-slate-900/80 border border-white/5 hover:border-amber-400/40 rounded-xl px-4 py-3 flex items-center justify-between transition-colors shadow-sm"
                >
                  <div className="flex items-center gap-3 min-w-0">
                    <span className="w-8 h-8 rounded-lg bg-slate-800 text-slate-300 font-black text-sm flex items-center justify-center shrink-0 border border-white/5">
                      #{item.rank}
                    </span>
                    <div className="min-w-0">
                      <h4 className="text-sm font-bold text-white truncate">
                        {item.name}
                      </h4>
                      <span className="text-xs text-slate-400 block">
                        {item.city ? `${item.city} • ` : ''} {item.table_number || item.customer_code}
                      </span>
                    </div>
                  </div>

                  <span className="text-base font-black text-amber-300 font-mono shrink-0">
                    {formatRupiah(item.total_spending)}
                  </span>
                </div>
              ))
            )}
          </div>
        </div>
      </main>

      {/* Bottom Running Marquee Ticker (Figma node 1:1435) */}
      <footer className="relative z-10 bg-slate-900/90 border border-amber-400/30 rounded-2xl py-3 px-6 shadow-lg flex items-center gap-4 overflow-hidden">
        <div className="flex items-center gap-2 shrink-0 bg-amber-400 text-slate-950 px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider">
          <Flame className="w-4 h-4 fill-slate-950" />
          <span>PENGUMUMAN</span>
        </div>
        <div className="overflow-hidden whitespace-nowrap flex-1">
          <p className="text-sm font-semibold text-amber-200 inline-block animate-marquee tracking-wide">
            {event?.running_ticker_text ||
              '🔥 Flash Deal Sesi PO Gathering SMS Sedang Berlangsung! Kumpulkan Order Sebanyak-banyaknya untuk Memenangkan Grand Prize Most Spender Malam Ini!'}
          </p>
        </div>
      </footer>
    </div>
  );
}
