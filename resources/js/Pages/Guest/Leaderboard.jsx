import React, { useState, useEffect } from 'react';
import { Head, Link, usePoll } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah, formatCountdown } from '../../utils/format';
import {
  Trophy,
  Clock,
  Gift,
  Crown,
  Medal,
  ArrowUpRight,
  Sparkles,
  Award,
  ShoppingBag,
  Layers,
  ChevronRight,
} from 'lucide-react';

export default function Leaderboard({
  leaderboard = [],
  leaderboards_by_manufacturer = {},
  manufacturers = [],
  stats_by_manufacturer = {},
  initialTab = 'all',
  currentPharmacy,
  currentMilestone,
  myStandings,
  event,
}) {
  // Auto-poll leaderboard every 5 seconds for real-time ranking updates
  usePoll(5000, {
    only: [
      'leaderboard',
      'leaderboards_by_manufacturer',
      'stats_by_manufacturer',
      'manufacturers',
      'myStandings',
      'currentMilestone',
      'event',
    ],
  });

  // Determine initial active tab (matches tab id: 'all' or manufacturer id)
  const resolveInitialTab = () => {
    if (!initialTab || initialTab === 'all') return 'all';
    const found = (manufacturers || []).find(
      (m) => m.slug === initialTab || String(m.id) === String(initialTab)
    );
    return found ? found.id : 'all';
  };

  const [activeTab, setActiveTab] = useState(resolveInitialTab);

  // Dynamic session resolution (per-manufacturer, multi-manufacturer, or overall)
  const resolveCurrentSessionInfo = () => {
    if (activeTab !== 'all') {
      const curM = (manufacturers || []).find((m) => m.id === activeTab);
      if (!curM || curM.is_ordering_open === false) {
        return {
          label: 'Sesi PO:',
          text: '00:00:00',
          subtext: 'Ditutup',
          colorClass: 'text-rose-600',
          isLive: false,
        };
      }
      if (curM.session_end_at) {
        const remaining = formatCountdown(curM.session_end_at);
        const isExpired = remaining === '00:00:00';
        return {
          label: 'Sesi PO:',
          text: remaining,
          subtext: isExpired ? 'Selesai' : null,
          colorClass: isExpired ? 'text-slate-500' : 'text-amber-600',
          isLive: !isExpired,
        };
      }
      return {
        label: 'Sesi PO:',
        text: 'Buka Penuh',
        subtext: null,
        colorClass: 'text-emerald-600',
        isLive: true,
      };
    }

    // Overall Tab
    const openMs = (manufacturers || []).filter((m) => m.is_ordering_open);
    const timedMs = openMs.filter(
      (m) => m.session_end_at && new Date(m.session_end_at) > new Date()
    );

    if (timedMs.length > 0) {
      const latestM = timedMs.reduce((prev, cur) =>
        new Date(cur.session_end_at) > new Date(prev.session_end_at) ? cur : prev
      );
      const remaining = formatCountdown(latestM.session_end_at);
      const isSingleFlash = timedMs.length === 1;

      return {
        label: isSingleFlash ? `Sesi ${latestM.name.replace(/^PT\.\s*/i, '')}:` : 'Sesi PO Aktif:',
        text: remaining,
        subtext: timedMs.length > 1 ? `(${timedMs.length} Pabrik)` : null,
        colorClass: 'text-amber-600',
        isLive: true,
      };
    }

    if (openMs.length > 0) {
      return {
        label: 'Sesi PO:',
        text: 'Buka Penuh',
        subtext: `(${openMs.length} Pabrik)`,
        colorClass: 'text-emerald-600',
        isLive: true,
      };
    }

    if (event?.session_end_at && new Date(event.session_end_at) > new Date()) {
      return {
        label: 'Sesi PO:',
        text: formatCountdown(event.session_end_at),
        subtext: null,
        colorClass: 'text-slate-900',
        isLive: true,
      };
    }

    return {
      label: 'Sesi PO:',
      text: '00:00:00',
      subtext: 'Ditutup',
      colorClass: 'text-slate-400',
      isLive: false,
    };
  };

  const [sessionInfo, setSessionInfo] = useState(resolveCurrentSessionInfo);

  useEffect(() => {
    setSessionInfo(resolveCurrentSessionInfo());
    const timer = setInterval(() => {
      setSessionInfo(resolveCurrentSessionInfo());
    }, 1000);
    return () => clearInterval(timer);
  }, [activeTab, manufacturers, event?.session_end_at]);

  // Tab definitions: [Overall, ...Manufacturers]
  const tabs = [
    { id: 'all', name: 'Semua Pabrik (Overall)', slug: 'semua', isOverall: true },
    ...(manufacturers || []).map((m) => ({
      id: m.id,
      name: m.name,
      slug: m.slug,
      is_ordering_open: m.is_ordering_open,
      session_end_at: m.session_end_at,
      isOverall: false,
    })),
  ];

  const activeTabObj = tabs.find((t) => t.id === activeTab) || tabs[0];

  // Resolve currently active leaderboard data
  const currentLeaderboard =
    activeTab === 'all'
      ? leaderboard
      : leaderboards_by_manufacturer?.[activeTab] || [];

  const top3 = currentLeaderboard.slice(0, 3);
  const remainingRanks = currentLeaderboard.slice(3, 10);
  const hasAnySpending = (top3[0]?.total_spending || 0) > 0;
  const activeRemaining = remainingRanks.filter((item) => item && (item.total_spending || 0) > 0);

  // User's standing for currently active tab
  let currentMyRank = null;
  let currentMySpending = 0;

  if (activeTab === 'all') {
    currentMyRank = myStandings?.overall?.rank || currentMilestone?.current_rank;
    currentMySpending = myStandings?.overall?.total_spending ?? (currentMilestone?.total_spending || 0);
  } else {
    const mStanding = myStandings?.manufacturers?.find((m) => m.id === activeTab);
    currentMyRank = mStanding?.rank || null;
    currentMySpending = mStanding?.total_spending || 0;
  }

  return (
    <GuestLayout pharmacy={currentPharmacy} milestone={currentMilestone} title="Papan Peringkat">
      <Head title="Papan Peringkat Most Spender" />

      {/* Live Status & Sesi Timer Strip (Figma node 1:2102) */}
      <div className="p-3.5 pb-2">
        <div className="bg-white rounded-2xl p-2.5 px-3.5 flex items-center justify-between border border-slate-200/90 shadow-xs">
          <div className="flex items-center gap-2">
            <span className="relative flex h-2.5 w-2.5">
              <span className={`animate-ping absolute inline-flex h-full w-full rounded-full ${sessionInfo.isLive ? 'bg-emerald-400' : 'bg-slate-400'} opacity-75`} />
              <span className={`relative inline-flex rounded-full h-2.5 w-2.5 ${sessionInfo.isLive ? 'bg-emerald-500' : 'bg-slate-400'}`} />
            </span>
            <span className={`text-[11px] font-bold ${sessionInfo.isLive ? 'text-emerald-700' : 'text-slate-500'} uppercase tracking-wider`}>
              LIVE TRACKING • AUTO-UPDATE
            </span>
          </div>

          <div className="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
            <Clock className={`w-3.5 h-3.5 ${sessionInfo.isLive ? 'text-amber-600' : 'text-slate-400'}`} />
            <span className="truncate max-w-[120px]">{sessionInfo.label}</span>
            <span className={`font-bold font-mono ${sessionInfo.colorClass}`}>{sessionInfo.text}</span>
            {sessionInfo.subtext && (
              <span className="text-[10px] text-slate-400 font-semibold">{sessionInfo.subtext}</span>
            )}
          </div>
        </div>
      </div>

      {/* Grand Prize Announcement Banner (Figma node 1:2115) */}
      <div className="px-3.5 mb-3.5">
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

      {/* Ringkasan Standing Apotek Anda (Multi-Kategori) */}
      {currentPharmacy && myStandings && (
        <div className="px-3.5 mb-3.5">
          <div className="bg-gradient-to-br from-slate-900 via-brand-950 to-slate-900 text-white rounded-2xl p-3.5 border border-brand-800/60 shadow-md">
            <div className="flex items-center justify-between mb-2.5 pb-2 border-b border-white/10">
              <div className="flex items-center gap-1.5">
                <Award className="w-4 h-4 text-amber-400" />
                <span className="text-[11px] font-bold uppercase tracking-wider text-amber-300">
                  STANDING APOTEK ANDA
                </span>
              </div>
              <span className="text-[10px] text-slate-300">
                Klik kartu untuk lihat papan peringkat
              </span>
            </div>

            {/* Overall & Top Standings Horizontal Scroll */}
            <div className="flex items-stretch gap-2 overflow-x-auto no-scrollbar pb-1">
              {/* Overall Pill */}
              <button
                type="button"
                onClick={() => setActiveTab('all')}
                className={`p-2.5 rounded-xl border text-left shrink-0 min-w-[140px] transition-all flex flex-col justify-between ${
                  activeTab === 'all'
                    ? 'bg-amber-400/20 border-amber-400 ring-2 ring-amber-400/30'
                    : 'bg-white/10 border-white/15 hover:bg-white/15'
                }`}
              >
                <div>
                  <span className="text-[10px] text-amber-300 font-bold block uppercase tracking-wide">
                    ⭐ Overall
                  </span>
                  <div className="text-sm font-black text-white mt-0.5">
                    {myStandings.overall?.rank ? `Rank #${myStandings.overall.rank}` : '-'}
                  </div>
                </div>
                <div className="text-[11px] font-bold text-amber-200 font-mono mt-1">
                  {formatRupiah(myStandings.overall?.total_spending || 0)}
                </div>
              </button>

              {/* Manufacturers Standings */}
              {myStandings.manufacturers?.map((m) => {
                const isActive = activeTab === m.id;
                const hasOrder = m.has_spending;

                return (
                  <button
                    key={m.id}
                    type="button"
                    onClick={() => setActiveTab(m.id)}
                    className={`p-2.5 rounded-xl border text-left shrink-0 min-w-[130px] transition-all flex flex-col justify-between ${
                      isActive
                        ? 'bg-brand-500/30 border-brand-400 ring-2 ring-brand-400/30'
                        : hasOrder
                        ? 'bg-white/10 border-white/15 hover:bg-white/15'
                        : 'bg-white/5 border-white/10 opacity-70 hover:opacity-90'
                    }`}
                  >
                    <div>
                      <span className="text-[10px] text-slate-300 font-semibold block truncate">
                        {m.name}
                      </span>
                      <div className="text-xs font-bold text-white mt-0.5 flex items-center gap-1">
                        {hasOrder ? (
                          <>
                            <span className="text-amber-300">Rank #{m.rank}</span>
                            {m.rank <= 3 && <span>🏅</span>}
                          </>
                        ) : (
                          <span className="text-slate-400 text-[11px]">Belum Ada PO</span>
                        )}
                      </div>
                    </div>
                    <div className="text-[10px] font-medium text-brand-200 font-mono mt-1 truncate">
                      {hasOrder ? formatRupiah(m.total_spending) : 'Rp 0'}
                    </div>
                  </button>
                );
              })}
            </div>
          </div>
        </div>
      )}

      {/* Sticky Tab Filter (Overall + Pabrikan) */}
      <div
        className="sticky z-20 bg-canvas/95 backdrop-blur-md pt-2 pb-2.5 mb-3 border-b border-slate-200/70 shadow-xs"
        style={{ top: 'calc(var(--header-height, 69px) - 1px)' }}
      >
        <div className="px-3.5">
          <div className="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
            {tabs.map((tab) => {
              const isActive = activeTab === tab.id;

              return (
                <button
                  key={tab.id}
                  type="button"
                  onClick={() => setActiveTab(tab.id)}
                  className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all shadow-xs flex items-center gap-1 ${
                    isActive
                      ? 'bg-brand-600 text-white shadow-sm'
                      : 'bg-white border border-slate-200/90 text-slate-600 hover:bg-slate-50'
                  }`}
                >
                  {tab.isOverall ? (
                    <>
                      <span>⭐</span>
                      <span>Semua Pabrik</span>
                    </>
                  ) : (
                    <>
                      <span>{tab.name}</span>
                      {tab.is_ordering_open === false && (
                        <span className="text-[9px] bg-rose-100 text-rose-700 px-1 py-0.2 rounded font-bold">
                          Tutup
                        </span>
                      )}
                      {tab.is_ordering_open && tab.session_end_at && (
                        <span className="text-[9px] bg-amber-100 text-amber-800 px-1 py-0.2 rounded font-bold">
                          ⚡
                        </span>
                      )}
                    </>
                  )}
                </button>
              );
            })}
          </div>
        </div>
      </div>

      {/* Top 3 Podium Area */}
      <div className="px-3.5 mb-5">
        <div className="flex items-center justify-between mb-2.5">
          <div className="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
            <Crown className="w-4 h-4 text-amber-500" />
            <span>
              {activeTab === 'all'
                ? 'TOP 3 MOST SPENDER GATHERING'
                : `TOP 3 SPENDER: ${activeTabObj.name.toUpperCase()}`}
            </span>
          </div>
          <span className="text-[10px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">
            Realtime
          </span>
        </div>

        {!hasAnySpending ? (
          /* Empty state for category with 0 orders yet */
          <div className="bg-white rounded-2xl p-6 text-center border border-slate-200/90 shadow-xs my-2">
            <div className="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-2.5">
              <Trophy className="w-6 h-6" />
            </div>
            <h4 className="font-bold text-sm text-slate-900 mb-1">
              Belum Ada Transaksi untuk {activeTabObj.name}
            </h4>
            <p className="text-xs text-slate-500 max-w-xs mx-auto mb-3.5">
              Jadilah apotek pertama yang memesan produk {activeTabObj.name} untuk langsung menempati posisi Juara 1!
            </p>
            <Link
              href={`/katalog?manufacturer=${activeTabObj.slug}`}
              className="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition-colors"
            >
              <ShoppingBag className="w-4 h-4" />
              <span>Buka Katalog {activeTabObj.name}</span>
            </Link>
          </div>
        ) : (
          /* Podium Grid */
          <div className="grid grid-cols-3 gap-2 items-end pt-3">
            {/* Rank 2 (Silver) */}
            <div className="bg-white rounded-2xl p-2.5 border border-slate-200/90 text-center flex flex-col items-center shadow-xs order-1 relative min-h-[140px] justify-between">
              <div className="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-black text-xs mb-1.5 shadow-xs border border-slate-200">
                #2
              </div>
              {top3[1] && (top3[1].total_spending || 0) > 0 ? (
                <>
                  <div className="w-full">
                    <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                      {top3[1].name}
                    </h4>
                    <span className="text-[10px] text-slate-400 block mb-1 truncate max-w-full">
                      {top3[1].city || 'Jawa Tengah'}
                    </span>
                  </div>
                  <span className="font-bold text-[11px] text-slate-700 font-mono block">
                    {formatRupiah(top3[1].total_spending)}
                  </span>
                </>
              ) : (
                <div className="my-auto py-2">
                  <span className="text-[11px] text-slate-400 block font-medium">Tersedia</span>
                  <span className="text-[9px] text-slate-300 block">Belum ada PO</span>
                </div>
              )}
            </div>

            {/* Rank 1 (Gold) */}
            <div className="bg-gradient-to-b from-amber-50/80 via-white to-white rounded-2xl p-3 border-2 border-amber-400 shadow-sm ring-4 ring-amber-400/10 text-center flex flex-col items-center order-2 relative -mt-3 min-h-[160px] justify-between">
              <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-slate-950 flex items-center justify-center font-black text-sm mb-1.5 shadow-xs mt-0.5">
                #1
              </div>
              {top3[0] && (top3[0].total_spending || 0) > 0 ? (
                <>
                  <div className="w-full">
                    <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                      {top3[0].name}
                    </h4>
                    <span className="text-[10px] text-amber-700 font-semibold block mb-1 truncate max-w-full">
                      {top3[0].city || 'Jawa Tengah'}
                    </span>
                  </div>
                  <span className="font-black text-xs text-brand-700 font-mono block">
                    {formatRupiah(top3[0].total_spending)}
                  </span>
                </>
              ) : (
                <div className="my-auto py-2">
                  <span className="text-[11px] text-slate-400 block font-medium">Tersedia</span>
                  <span className="text-[9px] text-slate-300 block">Belum ada PO</span>
                </div>
              )}
            </div>

            {/* Rank 3 (Bronze) */}
            <div className="bg-white rounded-2xl p-2.5 border border-slate-200/90 text-center flex flex-col items-center shadow-xs order-3 relative min-h-[140px] justify-between">
              <div className="w-8 h-8 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center font-black text-xs mb-1.5 shadow-xs border border-amber-200/80">
                #3
              </div>
              {top3[2] && (top3[2].total_spending || 0) > 0 ? (
                <>
                  <div className="w-full">
                    <h4 className="font-bold text-xs text-slate-900 line-clamp-1 leading-tight">
                      {top3[2].name}
                    </h4>
                    <span className="text-[10px] text-slate-400 block mb-1 truncate max-w-full">
                      {top3[2].city || 'Jawa Tengah'}
                    </span>
                  </div>
                  <span className="font-bold text-[11px] text-slate-700 font-mono block">
                    {formatRupiah(top3[2].total_spending)}
                  </span>
                </>
              ) : (
                <div className="my-auto py-2">
                  <span className="text-[11px] text-slate-400 block font-medium">Tersedia</span>
                  <span className="text-[9px] text-slate-300 block">Belum ada PO</span>
                </div>
              )}
            </div>
          </div>
        )}
      </div>

      {/* Rank 4 - 10 List */}
      {hasAnySpending && (
        <div className="px-3.5 pb-32">
          <div className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2.5 flex items-center justify-between">
            <span>PERINGKAT 4 — 10</span>
            {activeTab !== 'all' && (
              <span className="text-[10px] font-normal text-slate-500 lowercase">
                kategori {activeTabObj.name}
              </span>
            )}
          </div>

          {activeRemaining.length === 0 ? (
            <div className="bg-white rounded-2xl p-4 text-center border border-slate-200/80 shadow-xs">
              <p className="text-xs text-slate-500">
                Posisi 4 — 10 masih terbuka! Pesan produk{' '}
                <strong className="text-slate-800">{activeTabObj.name}</strong> untuk langsung masuk 10 besar.
              </p>
            </div>
          ) : (
            <div className="bg-white rounded-2xl border border-slate-200/90 shadow-xs divide-y divide-slate-100 overflow-hidden">
              {activeRemaining.map((item) => {
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
          )}
        </div>
      )}

      {/* Sticky Bottom My Rank Highlight */}
      {currentPharmacy && (
        <div className="fixed sm:absolute bottom-16 left-0 right-0 max-w-guest mx-auto p-4 z-40">
          <div className="bg-brand-900/95 backdrop-blur-md text-white p-3.5 px-4 rounded-2xl shadow-floating flex items-center justify-between border border-brand-700/50">
            <div className="flex items-center gap-3 min-w-0 pr-2">
              <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-400 to-amber-300 text-slate-950 font-black text-xs flex items-center justify-center shadow-sm shrink-0">
                {currentMyRank ? `#${currentMyRank}` : '-'}
              </div>
              <div className="min-w-0">
                <span className="text-[10px] text-brand-200 block uppercase font-bold tracking-wider truncate">
                  {activeTab === 'all'
                    ? 'Posisi Apotek Anda (Overall):'
                    : `Posisi di ${activeTabObj.name}:`}
                </span>
                <span className="text-xs font-bold text-white truncate max-w-[170px] block">
                  {currentPharmacy.name}
                </span>
              </div>
            </div>

            <div className="text-right shrink-0">
              {currentMySpending > 0 ? (
                <>
                  <span className="text-[10px] text-brand-200 block uppercase font-bold tracking-wider">
                    Total Belanja:
                  </span>
                  <span className="text-xs font-bold text-amber-300 font-mono">
                    {formatRupiah(currentMySpending)}
                  </span>
                </>
              ) : (
                <Link
                  href={`/katalog?manufacturer=${activeTabObj.slug}`}
                  className="inline-flex items-center gap-1 bg-amber-400 hover:bg-amber-300 text-slate-950 text-[11px] font-bold px-2.5 py-1.5 rounded-xl transition-colors shadow-xs"
                >
                  <span>Pesan Sekarang</span>
                  <ArrowUpRight className="w-3.5 h-3.5" />
                </Link>
              )}
            </div>
          </div>
        </div>
      )}
    </GuestLayout>
  );
}