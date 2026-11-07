import React, { useState, useEffect, useMemo } from 'react';
import { Head, Link, router, usePoll } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah, formatCountdown } from '../../utils/format';
import { useCart } from '../../hooks/useCart';
import QuantityStepper from '../../Components/QuantityStepper';
import { Search, ShoppingBag, ArrowRight, Sparkles, Receipt, Trophy, Gift, Ticket, X, Clock, Lock } from 'lucide-react';

export default function Catalog({
  pharmacy,
  milestone,
  currentRewards,
  ordersCount = 0,
  manufacturers,
  products,
  selectedManufacturer,
  searchQuery,
  event,
}) {
  const [search, setSearch] = useState(searchQuery || '');
  const { cart, totalItems, totalAmount, getItemQuantity, updateQuantity } = useCart();

  // Auto-poll catalog every 4 seconds for real-time manufacturer session updates
  usePoll(4000, {
    only: [
      'manufacturers',
      'products',
      'event',
      'milestone',
      'currentRewards',
      'ordersCount',
    ],
  });

  const isManufacturerOpen = (m) => {
    if (!m) return false;
    if (m.is_ordering_active === false) return false;
    if (m.session_end_at && new Date(m.session_end_at).getTime() <= Date.now()) return false;
    return m.is_ordering_open !== false;
  };

  // Sort manufacturers: Open (ON) sessions first, then closed (OFF), preserving sort order within groups
  const sortedManufacturers = useMemo(() => {
    return [...(manufacturers || [])].sort((a, b) => {
      const aOpen = isManufacturerOpen(a) ? 1 : 0;
      const bOpen = isManufacturerOpen(b) ? 1 : 0;

      if (aOpen !== bOpen) {
        return bOpen - aOpen; // 1 (ON) comes before 0 (OFF)
      }

      return (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.name.localeCompare(b.name);
    });
  }, [manufacturers]);

  const activeM = (manufacturers || []).find((m) => m.slug === selectedManufacturer);
  const hasAnyOpenManufacturer = (manufacturers || []).some((m) => isManufacturerOpen(m));
  const [mCountdown, setMCountdown] = useState(() => formatCountdown(activeM?.session_end_at));

  useEffect(() => {
    if (!activeM?.session_end_at) return;
    setMCountdown(formatCountdown(activeM.session_end_at));
    const timer = setInterval(() => {
      setMCountdown(formatCountdown(activeM.session_end_at));
    }, 1000);
    return () => clearInterval(timer);
  }, [activeM?.session_end_at]);

  const handleFilterManufacturer = (slug) => {
    router.get('/katalog', { manufacturer: slug, search }, { preserveState: true, preserveScroll: true });
  };

  const handleSearchSubmit = (e) => {
    e?.preventDefault();
    router.get('/katalog', { manufacturer: selectedManufacturer, search }, { preserveState: true, preserveScroll: true });
  };

  const hasVouchers = (currentRewards?.total_vouchers || 0) > 0;
  const physicalRewardEntries = Object.entries(currentRewards?.physical_rewards || {});
  const hasPhysicalRewards = physicalRewardEntries.length > 0;
  const choiceSelections = currentRewards?.choice_selections || [];
  const hasChoices = choiceSelections.length > 0;
  const exeltisLanso = currentRewards?.details?.exeltis?.lanso_box || 0;
  const exeltisSucra = currentRewards?.details?.exeltis?.sucra_fls || 0;
  const hasExeltis = exeltisLanso > 0 || exeltisSucra > 0;
  const hasAnyReward = hasVouchers || hasPhysicalRewards || hasChoices || hasExeltis;

  return (
    <GuestLayout pharmacy={pharmacy} milestone={milestone} title="Katalog Obat & Reward">
      <Head title="Katalog Belanja & Ringkasan Hadiah Gathering" />

      {/* Ringkasan Belanja & Reward Nyata Gathering (Opsi A) - Tidak sticky, scroll secara natural */}
      <div className="bg-gradient-to-br from-brand-900 via-brand-800 to-slate-900 text-white p-4 m-3.5 rounded-2xl shadow-lg relative z-0 overflow-hidden border border-brand-700/40">
        <div className="absolute top-0 right-0 -mr-6 -mt-6 w-32 h-32 bg-amber-400/10 rounded-full blur-2xl pointer-events-none" />

        {/* Top Header inside Banner */}
        <div className="flex items-center justify-between gap-2 mb-3 relative z-10">
          <div className="flex items-center gap-1.5 text-[11px] font-bold tracking-wider text-amber-300 uppercase">
            <Receipt className="w-3.5 h-3.5 text-amber-400" />
            <span>RINGKASAN BELANJA GATHERING</span>
          </div>

          <Link
            href="/leaderboard"
            className="inline-flex items-center gap-1 bg-amber-400/15 hover:bg-amber-400/25 text-amber-300 border border-amber-300/30 text-[11px] font-bold px-2.5 py-1 rounded-full transition-colors"
          >
            <Trophy className="w-3 h-3 text-amber-400" />
            <span>Rank #{milestone?.current_rank || '-'}</span>
            <span className="text-amber-200/80 text-[10px]">→</span>
          </Link>
        </div>

        {/* 2-Column Metrics */}
        <div className="grid grid-cols-2 gap-2.5 relative z-10 mb-3">
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/10">
            <span className="text-[10px] text-brand-200 block uppercase font-medium">
              Total Belanja
            </span>
            <div className="text-base font-bold text-white mt-0.5 truncate">
              {formatRupiah(milestone?.total_spending || 0)}
            </div>
            {totalAmount > 0 && (
              <span className="text-[10px] text-emerald-300 font-medium block truncate">
                +{formatRupiah(totalAmount)} di keranjang
              </span>
            )}
          </div>

          <Link
            href="/pesanan"
            className="bg-white/10 hover:bg-white/15 backdrop-blur-sm rounded-xl p-2.5 border border-white/10 transition-colors block"
          >
            <span className="text-[10px] text-amber-200 block uppercase font-medium">
              Total Voucher
            </span>
            <div className="text-base font-bold text-amber-300 mt-0.5 truncate">
              {hasVouchers ? formatRupiah(currentRewards.total_vouchers) : 'Rp 0'}
            </div>
            <span className="text-[10px] text-amber-200/90 font-medium flex items-center gap-1">
              <span>{ordersCount > 0 ? `${ordersCount} PO Terkirim` : 'Belum ada PO'}</span>
              <span>→</span>
            </span>
          </Link>
        </div>

        {/* Reward Status & Highlights */}
        <div className="relative z-10 pt-2 border-t border-white/15">
          {hasAnyReward ? (
            <div className="space-y-1.5">
              <div className="flex items-center justify-between">
                <span className="text-[10px] text-amber-200 font-semibold uppercase tracking-wider flex items-center gap-1">
                  <Gift className="w-3 h-3 text-amber-400" />
                  <span>Hadiah &amp; Bonus Diraih:</span>
                </span>
                <Link
                  href="/pesanan"
                  className="text-[10px] font-bold text-amber-300 hover:text-amber-200 underline underline-offset-2"
                >
                  Lihat Rincian →
                </Link>
              </div>
              <div className="flex flex-wrap gap-1.5">
                {hasVouchers && (
                  <span className="bg-amber-400/20 px-2 py-0.5 rounded-lg text-[10px] font-semibold text-amber-200 border border-amber-300/30 flex items-center gap-1">
                    <Ticket className="w-3 h-3 text-amber-300" />
                    <span>Voucher: {formatRupiah(currentRewards.total_vouchers)}</span>
                  </span>
                )}
                {physicalRewardEntries.map(([gift, qty]) => (
                  <span
                    key={gift}
                    className="bg-white/15 backdrop-blur-md px-2 py-0.5 rounded-lg text-[10px] font-semibold text-white border border-white/20 flex items-center gap-1"
                  >
                    <span>🎁</span>
                    <span>{gift}: {qty} pcs</span>
                  </span>
                ))}
                {choiceSelections.map((cs, idx) => (
                  <span
                    key={idx}
                    className="bg-amber-400/20 px-2 py-0.5 rounded-lg text-[10px] font-semibold text-amber-200 border border-amber-300/30 flex items-center gap-1"
                  >
                    <span>⭐</span>
                    <span>{cs.selected_option}</span>
                  </span>
                ))}
                {hasExeltis && (
                  <span className="bg-emerald-400/20 px-2 py-0.5 rounded-lg text-[10px] font-semibold text-emerald-200 border border-emerald-300/30 flex items-center gap-1">
                    <span>✨</span>
                    <span>Exeltis ({exeltisLanso} Box / {exeltisSucra} Botol): Bebas Pilih Hadiah</span>
                  </span>
                )}
              </div>
            </div>
          ) : (
            <p className="text-[11px] text-brand-100/90 flex items-center gap-1.5 leading-snug">
              <span>💡</span>
              <span>
                Pilih produk dari 9 pabrikan di bawah untuk mengumpulkan hadiah fisik &amp; voucher belanja malam ini.
              </span>
            </p>
          )}
        </div>
      </div>

      {/* Sesi PO Inactive Notice Banner - Only shown when ALL manufacturers are closed */}
      {!hasAnyOpenManufacturer && (
        <div className="mx-3.5 mb-2 bg-amber-50 border border-amber-200/90 rounded-2xl p-3 flex items-center gap-2.5 text-xs text-amber-900 shadow-xs">
          <Clock className="w-4 h-4 text-amber-600 shrink-0" />
          <span className="text-[11px] font-medium leading-tight">
            {event?.ordering_status === 'scheduled'
              ? 'Sesi Pemesanan belum dibuka. Anda dapat melihat produk & menyiapkan keranjang terlebih dahulu.'
              : event?.ordering_status === 'ended'
              ? 'Sesi Pemesanan gathering telah berakhir.'
              : 'Sesi Pemesanan sedang ditutup sementara oleh panitia. Menunggu sesi pabrikan dibuka.'}
          </span>
        </div>
      )}

      {/* Sticky Search & Filter Chips Bar */}
      <div 
        className="sticky z-20 bg-canvas/95 backdrop-blur-md pt-2 pb-2.5 mb-3 border-b border-slate-200/70 shadow-xs transition-shadow"
        style={{ top: 'calc(var(--header-height, 69px) - 1px)' }}
      >
        {/* Search Input Bar */}
        <div className="px-3.5 mb-2.5">
          <form onSubmit={handleSearchSubmit} className="relative">
            <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              placeholder="Cari obat, nama pabrik, atau SKU..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full pl-10 pr-9 py-2.5 bg-white rounded-xl border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 shadow-xs"
            />
            {search && (
              <button
                type="button"
                onClick={() => {
                  setSearch('');
                  router.get('/katalog', { manufacturer: selectedManufacturer, search: '' }, { preserveState: true, preserveScroll: true });
                }}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 rounded-full hover:bg-slate-100 transition-colors"
                title="Hapus pencarian"
              >
                <X className="w-4 h-4" />
              </button>
            )}
          </form>
        </div>

        {/* Manufacturer Tabs Horizontal Pill Filter (Figma node 1:440) */}
        <div className="px-3.5">
          <div className="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
            <button
              type="button"
              onClick={() => handleFilterManufacturer('semua')}
              className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all shadow-xs ${
                selectedManufacturer === 'semua'
                  ? 'bg-brand-600 text-white shadow-sm'
                  : 'bg-white border border-slate-200/90 text-slate-600 hover:bg-slate-50'
              }`}
            >
              Semua Pabrik
            </button>
            {sortedManufacturers.map((m) => {
              const isOpen = isManufacturerOpen(m);
              const isClosed = !isOpen;
              const hasTimer = isOpen && m.session_end_at;

              return (
                <button
                  key={m.id}
                  type="button"
                  onClick={() => handleFilterManufacturer(m.slug)}
                  className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all shadow-xs flex items-center gap-1.5 ${
                    selectedManufacturer === m.slug
                      ? 'bg-brand-600 text-white shadow-sm'
                      : 'bg-white border border-slate-200/90 text-slate-600 hover:bg-slate-50'
                  }`}
                >
                  <span>{m.name}</span>
                  {isClosed && (
                    <span className="text-[9px] bg-rose-100 text-rose-700 px-1 py-0.2 rounded font-bold">
                      Tutup
                    </span>
                  )}
                  {hasTimer && (
                    <span className="text-[9px] bg-amber-100 text-amber-800 px-1 py-0.2 rounded font-bold">
                      ⚡ Flash
                    </span>
                  )}
                </button>
              );
            })}
          </div>
        </div>
      </div>

      {/* Product List */}
      <div className="px-3.5 space-y-3 pb-28">
        {/* Active Manufacturer Specific Session Banner */}
        {activeM && !isManufacturerOpen(activeM) && (
          <div className="bg-rose-50 border border-rose-200/90 rounded-2xl p-3 flex items-center gap-2.5 text-xs text-rose-900 shadow-xs mb-3">
            <Lock className="w-4 h-4 text-rose-600 shrink-0" />
            <span className="text-[11px] font-medium leading-tight">
              {activeM.ordering_status === 'ended'
                ? `Sesi pemesanan untuk ${activeM.name} telah berakhir.`
                : `Sesi pemesanan untuk ${activeM.name} sedang ditutup sementara oleh panitia.`}
            </span>
          </div>
        )}

        {activeM && isManufacturerOpen(activeM) && activeM.session_end_at && (
          <div className="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 text-white rounded-2xl p-3 flex items-center justify-between shadow-sm mb-3">
            <div className="flex items-center gap-2">
              <Clock className="w-4 h-4 text-amber-200" />
              <span className="text-xs font-bold uppercase tracking-wider">
                Flash Sesi {activeM.name}
              </span>
            </div>
            <div className="text-xs font-black font-mono bg-black/20 px-2.5 py-1 rounded-lg">
              Sisa {mCountdown}
            </div>
          </div>
        )}

        {products.length === 0 ? (
          <div className="bg-white rounded-2xl p-8 text-center border border-slate-200/80 my-4 shadow-sm">
            <ShoppingBag className="w-12 h-12 text-slate-300 mx-auto mb-2" />
            <h4 className="font-bold text-sm text-slate-800 mb-1">Produk Tidak Ditemukan</h4>
            <p className="text-xs text-slate-500">
              Tidak ada produk yang sesuai dengan filter atau kata kunci pencarian Anda.
            </p>
          </div>
        ) : (
          products.map((product) => {
            const qty = getItemQuantity(product.id);
            const isProductOrderDisabled = !isManufacturerOpen(product.manufacturer);

            return (
              <div
                key={product.id}
                className="bg-white rounded-2xl p-3.5 border border-slate-200/90 shadow-xs hover:shadow-md transition-all flex flex-col justify-between"
              >
                <div className="flex items-start justify-between gap-2 mb-2">
                  <div className="flex-1 min-w-0">
                    <span className="inline-block text-[10px] font-bold text-brand-600 uppercase tracking-wider bg-brand-50 px-2 py-0.5 rounded mb-1">
                      {product.manufacturer?.name}
                    </span>
                    <h3 className="font-semibold text-sm text-slate-900 leading-tight">
                      {product.name}
                    </h3>
                    {product.description && (
                      <p className="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                        {product.description}
                      </p>
                    )}
                  </div>
                  <span className="text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-1 rounded-md shrink-0 whitespace-nowrap">
                    {product.uom}
                  </span>
                </div>

                <div className="flex items-center justify-between pt-2 border-t border-slate-100 mt-1">
                  <div>
                    <span className="text-[10px] text-slate-400 block">Harga Gathering</span>
                    <span className="font-bold text-sm text-brand-700">
                      {formatRupiah(product.price)}
                    </span>
                  </div>

                  {/* Quantity Stepper or Closed Badge */}
                  {isProductOrderDisabled ? (
                    <span className="text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200 px-3 py-1.5 rounded-xl">
                      Sesi PO Ditutup
                    </span>
                  ) : (
                    <QuantityStepper
                      value={qty}
                      onChange={(newQty) => updateQuantity(product, newQty)}
                      min={0}
                      max={9999}
                    />
                  )}
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* Floating Checkout Cart Bar (Figma node 1:420) */}
      {totalItems > 0 && (
        <div className="fixed sm:absolute bottom-16 left-0 right-0 max-w-guest px-4 py-4 z-40 animate-in slide-in-from-bottom duration-200">
          <Link
            href="/keranjang"
            className="w-full bg-brand-900/95 backdrop-blur-md text-white p-3.5 px-4 rounded-2xl shadow-floating flex items-center justify-between hover:bg-brand-900 transition-all border border-brand-700/50"
          >
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-md bg-brand-700 flex flex-col items-center justify-center text-amber-300 font-bold text-sm shadow-sm gap-0">
                <span className="text-[10px] text-brand-200 uppercase font-bold tracking-wider block">QTY</span>
                <span>{totalItems}</span>
              </div>
              <div>
                <span className="text-[10px] text-brand-200 uppercase font-bold tracking-wider block">
                  Total Estimasi PO
                </span>
                <span className="text-base font-bold text-white">
                  {formatRupiah(totalAmount)}
                </span>
              </div>
            </div>

            <div className="flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs py-2 px-3.5 rounded-xl shadow-sm">
              <span>Checkout</span>
              <ArrowRight className="w-4 h-4" />
            </div>
          </Link>
        </div>
      )}
    </GuestLayout>
  );
}
