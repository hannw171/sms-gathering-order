import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah } from '../../utils/format';
import { useCart } from '../../hooks/useCart';
import { Search, Plus, Minus, ShoppingBag, ArrowRight, Award, Sparkles } from 'lucide-react';

export default function Catalog({ pharmacy, milestone, manufacturers, products, selectedManufacturer, searchQuery, event }) {
  const [search, setSearch] = useState(searchQuery || '');
  const { cart, totalItems, totalAmount, getItemQuantity, updateQuantity } = useCart();

  const handleFilterManufacturer = (slug) => {
    router.get('/katalog', { manufacturer: slug, search }, { preserveState: true, preserveScroll: true });
  };

  const handleSearchSubmit = (e) => {
    e?.preventDefault();
    router.get('/katalog', { manufacturer: selectedManufacturer, search }, { preserveState: true, preserveScroll: true });
  };

  // Calculate dynamic projected tier if current cart items are checked out
  const projectedTotal = (milestone?.total_spending || 0) + totalAmount;
  const nextTier = milestone?.next_tier;
  const currentTier = milestone?.unlocked_tier;

  return (
    <GuestLayout pharmacy={pharmacy} milestone={milestone} title="Katalog Obat & Reward">
      <Head title="Katalog Belanja & Dynamic Reward Tracker" />

      {/* Dynamic Reward Tracker Banner (Figma node 1:420) */}
      <div className="bg-gradient-to-r from-brand-700 via-brand-600 to-brand-800 text-white p-4 m-3.5 rounded-2xl shadow-md relative overflow-hidden">
        <div className="absolute top-0 right-0 -mr-6 -mt-6 w-32 h-32 bg-amber-400/15 rounded-full blur-2xl pointer-events-none" />

        <div className="flex items-start justify-between gap-3 mb-2.5 relative z-10">
          <div>
            <div className="flex items-center gap-1.5 text-[11px] font-bold tracking-wider text-amber-300 uppercase">
              <Sparkles className="w-3.5 h-3.5 text-amber-400" />
              <span>DYNAMIC REWARD TRACKER</span>
            </div>
            <div className="text-lg font-bold text-white mt-0.5">
              {formatRupiah(milestone?.total_spending || 0)}
              {totalAmount > 0 && (
                <span className="text-xs font-normal text-emerald-300 ml-1.5 font-mono">
                  (+{formatRupiah(totalAmount)} di keranjang)
                </span>
              )}
            </div>
          </div>

          <div className="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-2.5 py-1 text-right">
            <span className="block text-[10px] text-brand-100 font-medium">Reward Saat Ini</span>
            <span className="text-xs font-bold text-amber-300 flex items-center gap-1 justify-end">
              <span>{currentTier ? currentTier.badge_icon : '⏳'}</span>
              <span>{currentTier ? currentTier.reward_description : 'Belum Ada'}</span>
            </span>
          </div>
        </div>

        {/* Progress Bar towards Next Tier */}
        {nextTier ? (
          <div className="relative z-10 mt-1">
            <div className="flex justify-between items-center text-[11px] text-brand-100 mb-1">
              <span>Target: <strong className="text-white">{nextTier.reward_description}</strong></span>
              <span>{milestone.progress_percentage}%</span>
            </div>
            <div className="w-full bg-black/20 h-2.5 rounded-full overflow-hidden p-0.5 border border-white/10">
              <div
                className="bg-gradient-to-r from-emerald-400 to-amber-300 h-full rounded-full transition-all duration-500 shadow-sm"
                style={{ width: `${Math.min(100, milestone.progress_percentage)}%` }}
              />
            </div>
            <p className="text-[11px] text-brand-100/90 mt-1.5 flex items-center gap-1">
              <span>🚀</span>
              <span>
                Belanja <strong>{formatRupiah(milestone.amount_to_next_tier)}</strong> lagi untuk buka <strong>{nextTier.name}</strong>!
              </span>
            </p>
          </div>
        ) : (
          <div className="bg-emerald-500/20 border border-emerald-400/30 rounded-xl p-2 text-center text-xs text-emerald-100 font-semibold">
            🎉 Selamat! Anda telah mencapai Tier Reward Tertinggi Malam Ini!
          </div>
        )}
      </div>

      {/* Search Input Bar */}
      <div className="px-3.5 mb-3">
        <form onSubmit={handleSearchSubmit} className="relative">
          <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Cari obat, nama pabrik, atau SKU..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 bg-white rounded-xl border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 shadow-xs"
          />
        </form>
      </div>

      {/* Manufacturer Tabs Horizontal Pill Filter (Figma node 1:440) */}
      <div className="px-3.5 mb-3.5">
        <div className="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
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
          {manufacturers.map((m) => (
            <button
              key={m.id}
              type="button"
              onClick={() => handleFilterManufacturer(m.slug)}
              className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all shadow-xs ${
                selectedManufacturer === m.slug
                  ? 'bg-brand-600 text-white shadow-sm'
                  : 'bg-white border border-slate-200/90 text-slate-600 hover:bg-slate-50'
              }`}
            >
              {m.name}
            </button>
          ))}
        </div>
      </div>

      {/* Product List */}
      <div className="px-3.5 space-y-3 pb-28">
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

                  {/* Quantity Stepper (Figma node 1:440) */}
                  <div className="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                    <button
                      type="button"
                      onClick={() => updateQuantity(product, Math.max(0, qty - 1))}
                      disabled={qty === 0}
                      className="w-7 h-7 rounded-lg bg-white flex items-center justify-center text-slate-700 hover:bg-slate-200 disabled:opacity-30 disabled:hover:bg-white shadow-xs transition-colors"
                      aria-label="Kurang"
                    >
                      <Minus className="w-3.5 h-3.5" />
                    </button>
                    <span className="w-8 text-center text-xs font-bold text-slate-800">
                      {qty}
                    </span>
                    <button
                      type="button"
                      onClick={() => updateQuantity(product, qty + 1)}
                      className="w-7 h-7 rounded-lg bg-brand-600 flex items-center justify-center text-white hover:bg-brand-700 shadow-xs transition-colors"
                      aria-label="Tambah"
                    >
                      <Plus className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* Floating Checkout Cart Bar (Figma node 1:420) */}
      {totalItems > 0 && (
        <div className="fixed sm:absolute bottom-16 left-0 right-0 max-w-guest mx-auto px-4 z-40 animate-in slide-in-from-bottom duration-200">
          <Link
            href="/keranjang"
            className="w-full bg-brand-900/95 backdrop-blur-md text-white p-3.5 px-4 rounded-2xl shadow-floating flex items-center justify-between hover:bg-brand-900 transition-all border border-brand-700/50"
          >
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-brand-700 flex items-center justify-center text-amber-300 font-bold text-sm shadow-inner">
                {totalItems}
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
