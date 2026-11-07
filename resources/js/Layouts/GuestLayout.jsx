import React, { useRef, useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { ShoppingBag, ShoppingCart, ClipboardList, Trophy, LogOut } from 'lucide-react';
import { useCart } from '../hooks/useCart';

export default function GuestLayout({ children, pharmacy, milestone, title }) {
  const { url } = usePage();
  const { totalItems } = useCart();
  const headerRef = useRef(null);
  const [headerHeight, setHeaderHeight] = useState(69);

  useEffect(() => {
    if (!pharmacy || !headerRef.current) {
      setHeaderHeight(0);
      return;
    }

    const updateHeight = () => {
      if (headerRef.current) {
        setHeaderHeight(headerRef.current.offsetHeight);
      }
    };

    updateHeight();

    if (typeof ResizeObserver !== 'undefined') {
      const observer = new ResizeObserver(updateHeight);
      observer.observe(headerRef.current);
      return () => observer.disconnect();
    }
  }, [pharmacy, milestone]);

  const isCatalog = url.startsWith('/katalog');
  const isCart = url.startsWith('/keranjang');
  const isOrders = url.startsWith('/pesanan');
  const isLeaderboard = url.startsWith('/leaderboard');

  return (
    <div className="min-h-screen bg-slate-900 flex justify-center items-start py-0 sm:py-6 selection:bg-brand-500 selection:text-white">
      {/* Mobile-first viewport container (matches Figma 390-448px) */}
      <div 
        className="w-full max-w-guest min-h-screen bg-canvas text-slate-900 flex flex-col relative shadow-2xl overflow-x-clip border-x border-slate-200"
        style={{ '--header-height': `${headerHeight}px` }}
      >
        
        {/* Pharmacy Top Context Strip (Figma node 1:424) */}
        {pharmacy && (
          <header 
            ref={headerRef}
            className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 py-3 flex items-center justify-between shadow-sm"
          >
            <div className="flex flex-col min-w-0 pr-2">
              <div className="flex items-center gap-2">
                <span className="font-semibold text-[15px] text-brand-900 truncate tracking-tight">
                  {pharmacy.name}
                </span>
              </div>
              {milestone && (
                <div className="flex items-center gap-1.5 mt-0.5">
                  <div className="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200/80 text-[11px] font-bold px-2 py-0.5 rounded-full">
                    <span>🏆</span>
                    <span>Rank #{milestone.current_rank || '-'} PBF Gathering</span>
                  </div>
                </div>
              )}
            </div>

            <div className="flex items-center gap-1.5 shrink-0">
              <Link
                href="/keranjang"
                className="relative p-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                title="Keranjang Belanja"
              >
                <ShoppingCart className="w-5 h-5 text-brand-700" />
                {totalItems > 0 && (
                  <span className="absolute -top-1 -right-1 bg-emerald-600 text-white text-[11px] font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-sm animate-pulse">
                    {totalItems}
                  </span>
                )}
              </Link>

              <button
                type="button"
                onClick={() => {
                  if (window.confirm('Keluar dari sesi apotek ini?')) {
                    router.post('/logout-guest');
                  }
                }}
                className="p-2.5 rounded-full bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors"
                title="Keluar / Ganti Akun"
              >
                <LogOut className="w-4 h-4" />
              </button>
            </div>
          </header>
        )}

        {/* Main Content Area */}
        <main className="flex-1 pb-24">
          {children}
        </main>

        {/* Bottom Navigation Bar */}
        {pharmacy && (
          <nav className="fixed bottom-0 left-0 right-0 max-w-guest mx-auto z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 flex items-center justify-around py-2 px-4 shadow-lg">
            <Link
              href="/katalog"
              className={`flex flex-col items-center gap-1 py-1 px-3 rounded-lg transition-colors ${
                isCatalog ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'
              }`}
            >
              <ShoppingBag className={`w-5 h-5 ${isCatalog ? 'stroke-[2.5]' : ''}`} />
              <span className="text-[11px]">Katalog</span>
            </Link>

            <Link
              href="/keranjang"
              className={`relative flex flex-col items-center gap-1 py-1 px-3 rounded-lg transition-colors ${
                isCart ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'
              }`}
            >
              <ShoppingCart className={`w-5 h-5 ${isCart ? 'stroke-[2.5]' : ''}`} />
              <span className="text-[11px]">Keranjang</span>
              {totalItems > 0 && (
                <span className="absolute top-0 right-2 w-2 h-2 rounded-full bg-emerald-500" />
              )}
            </Link>

            <Link
              href="/pesanan"
              className={`flex flex-col items-center gap-1 py-1 px-3 rounded-lg transition-colors ${
                isOrders ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'
              }`}
            >
              <ClipboardList className={`w-5 h-5 ${isOrders ? 'stroke-[2.5]' : ''}`} />
              <span className="text-[11px]">Pesanan</span>
            </Link>

            <Link
              href="/leaderboard"
              className={`flex flex-col items-center gap-1 py-1 px-3 rounded-lg transition-colors ${
                isLeaderboard ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'
              }`}
            >
              <Trophy className={`w-5 h-5 ${isLeaderboard ? 'stroke-[2.5]' : ''}`} />
              <span className="text-[11px]">Peringkat</span>
            </Link>
          </nav>
        )}
      </div>
    </div>
  );
}
