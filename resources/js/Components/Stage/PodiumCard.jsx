import React from 'react';
import { Crown, Trophy, Medal, Award, MapPin } from 'lucide-react';
import { formatNumber } from '../../utils/format';

/**
 * Reusable Podium Card Component for Stage Leaderboard
 *
 * @param {number} rank - Podium rank (1, 2, or 3)
 * @param {object|null} item - Pharmacy ranking data { name, city, table_number, total_spending }
 * @param {string} categoryName - Active category or manufacturer name
 * @param {string} height - Optional custom height class override
 * @param {string} className - Optional additional container class
 * @param {boolean} showMeja - Whether to display table number
 * @param {string} emptyLabel - Placeholder text when no order yet
 */
export default function PodiumCard({
  rank = 1,
  item = null,
  categoryName = 'Semua Pabrik',
  height,
  className = '',
  emptyLabel = 'Menunggu Order...',
}) {
  const isRank1 = rank === 1;
  const isRank2 = rank === 2;
  const isRank3 = rank === 3;

  // Calibrated card heights to ensure full vertical fit without viewport clipping
  const defaultHeight = isRank1
    ? 'h-[350px] lg:h-[380px]'
    : isRank2
    ? 'h-[285px] lg:h-[310px]'
    : 'h-[250px] lg:h-[270px]';

  const cardHeight = height || defaultHeight;

  const hasSpending = item && !isNaN(item.total_spending) && item.total_spending > 0;

  // Category clean title
  const isOverall = categoryName.toLowerCase().includes('semua');
  const badgeTitle = isRank1
    ? isOverall
      ? 'GRAND CHAMPION'
      : `JUARA 1 ${categoryName}`
    : isRank2
    ? 'RUNNER UP'
    : 'JUARA 3';

  return (
    <div className={`w-full flex flex-col items-center select-none ${className}`}>
      {/* ========================================================================= */}
      {/* TOP BADGE & CROWN                                                         */}
      {/* ========================================================================= */}
      {isRank1 && (
        <div className="flex flex-col items-center mb-2 shrink-0">
          <Crown className="w-8 h-8 lg:w-10 lg:h-10 text-amber-500 animate-bounce -mb-1 filter drop-shadow-sm" />
          <div className="w-16 h-16 lg:w-18 lg:h-18 rounded-2xl bg-gradient-to-tr from-amber-500 via-amber-400 to-amber-300 border-4 border-amber-200 flex items-center justify-center text-slate-950 font-black text-2xl lg:text-3xl shadow-stage-gold">
            #1
          </div>
        </div>
      )}

      {isRank2 && (
        <div className="w-13 h-13 lg:w-15 lg:h-15 rounded-2xl bg-gradient-to-tr from-slate-200 to-slate-100 border-2 border-slate-300 text-slate-700 flex items-center justify-center font-black text-xl lg:text-2xl shadow-sm mb-2.5 shrink-0">
          #2
        </div>
      )}

      {isRank3 && (
        <div className="w-13 h-13 lg:w-15 lg:h-15 rounded-2xl bg-gradient-to-tr from-amber-100 to-amber-50 border-2 border-amber-300/80 text-amber-900 flex items-center justify-center font-black text-xl lg:text-2xl shadow-sm mb-2.5 shrink-0">
          #3
        </div>
      )}

      {/* ========================================================================= */}
      {/* CARD BODY                                                                 */}
      {/* ========================================================================= */}
      <div
        className={`w-full text-center flex flex-col justify-between transition-all duration-300 ${cardHeight} ${
          isRank1
            ? 'bg-gradient-to-b from-amber-50/90 via-white to-amber-50/30 border-2 border-amber-400 rounded-3xl p-4 lg:p-5 shadow-xl ring-8 ring-amber-400/10'
            : isRank2
            ? 'bg-gradient-to-b from-slate-50/60 via-white to-white border border-slate-300/90 rounded-3xl p-4 lg:p-5 shadow-md'
            : 'bg-gradient-to-b from-orange-50/30 via-white to-white border border-amber-200/90 rounded-3xl p-4 lg:p-5 shadow-md'
        }`}
      >
        {/* ----------------- Card Top Header ----------------- */}
        <div className="flex flex-col items-center">
          {/* Rank Ribbon Pill */}
          <span
            className={`text-[10px] lg:text-xs font-black px-3.5 py-1 rounded-full uppercase tracking-wider inline-flex items-center gap-1 mb-2 shadow-2xs ${
              isRank1
                ? 'bg-gradient-to-r from-amber-500 to-amber-400 text-slate-950 shadow-xs'
                : isRank2
                ? 'bg-slate-200/80 text-slate-700 border border-slate-300'
                : 'bg-amber-100/80 text-amber-900 border border-amber-200'
            }`}
          >
            {isRank1 && <span>👑</span>}
            {isRank2 && <span>🥈</span>}
            {isRank3 && <span>🥉</span>}
            <span>{badgeTitle}</span>
          </span>

          {/* Pharmacy Name */}
          <h3
            className={`font-black line-clamp-2 leading-snug w-full px-1 ${
              isRank1
                ? 'text-lg lg:text-2xl text-slate-950'
                : 'text-base lg:text-xl text-slate-900'
            }`}
            title={item?.name || emptyLabel}
          >
            {item?.name || emptyLabel}
          </h3>

          {/* Location & Table Badge Pill */}
          <div className="mt-1.5 flex items-center justify-center">
            <span
              className={`inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-0.5 rounded-full border ${
                isRank1
                  ? 'bg-amber-100/70 text-amber-900 border-amber-300/60'
                  : isRank2
                  ? 'bg-slate-100 text-slate-600 border-slate-200'
                  : 'bg-amber-50 text-amber-800 border-amber-200/70'
              }`}
            >
              <MapPin className="w-3 h-3 shrink-0 opacity-70" />
              <span>{item?.city || 'Apotek Rekanan'}</span>
            </span>
          </div>
        </div>

        {/* ----------------- Decorative Center Emblem (Eliminates Awkward Dead Space) ----------------- */}
        <div className="my-auto py-2 flex items-center justify-center">
          {isRank1 ? (
            <div className="w-14 h-14 lg:w-16 lg:h-16 rounded-2xl bg-gradient-to-tr from-amber-100 to-amber-50 border border-amber-300/60 flex items-center justify-center shadow-inner text-amber-600/80">
              <Trophy className="w-7 h-7 lg:w-8 lg:h-8 stroke-[2.2]" />
            </div>
          ) : isRank2 ? (
            <div className="w-11 h-11 lg:w-13 lg:h-13 rounded-2xl bg-slate-100/80 border border-slate-200 flex items-center justify-center shadow-inner text-slate-500">
              <Medal className="w-6 h-6 lg:w-7 lg:h-7 stroke-[2]" />
            </div>
          ) : (
            <div className="w-10 h-10 lg:w-12 lg:h-12 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center justify-center shadow-inner text-amber-700/70">
              <Award className="w-5 h-5 lg:w-6 lg:h-6 stroke-[2]" />
            </div>
          )}
        </div>

        {/* ----------------- Bottom Highlighted Prize / Transaction Box ----------------- */}
        <div
          className={`w-full rounded-2xl p-2.5 lg:p-3 border shadow-xs flex flex-col items-center ${
            isRank1
              ? 'bg-gradient-to-b from-amber-50 to-white border-amber-300/80'
              : isRank2
              ? 'bg-slate-50 border-slate-200/90'
              : 'bg-amber-50/60 border-amber-200/80'
          }`}
        >
          <span
            className={`text-[10px] lg:text-[11px] font-bold uppercase tracking-widest block mb-0.5 ${
              isRank1 ? 'text-amber-800' : isRank3 ? 'text-amber-800/80' : 'text-slate-500'
            }`}
          >
            {isRank1 ? 'Total Akumulasi PO' : 'Total Transaksi'}
          </span>

          {/* Single-Line Nominal with Balanced Prefix */}
          <div className="w-full flex items-baseline justify-center whitespace-nowrap overflow-hidden px-1">
            <span
              className={`font-bold select-none mr-1.5 shrink-0 ${
                isRank1
                  ? 'text-sm lg:text-base text-amber-700/80'
                  : 'text-xs lg:text-sm text-slate-400'
              }`}
            >
              Rp
            </span>

            <span
              className={`font-black font-mono tracking-tight shrink-0 ${
                isRank1
                  ? 'text-xl lg:text-3xl text-brand-700'
                  : 'text-lg lg:text-2xl text-slate-800'
              }`}
            >
              {hasSpending ? formatNumber(item.total_spending) : '-'}
            </span>
          </div>
        </div>
      </div>
    </div>
  );
}
