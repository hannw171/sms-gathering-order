import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { formatRupiah } from '../../utils/format';
import {
  Gift,
  Ticket,
  ClipboardList,
  ChevronDown,
  ChevronUp,
  CheckCircle2,
  XCircle,
  Clock,
  ShoppingBag,
  Building2,
  Sparkles,
  Package,
} from 'lucide-react';

export default function Orders({ pharmacy, milestone, rewards, orders = [] }) {
  const [activeTab, setActiveTab] = useState('rewards');
  const [expandedOrderIds, setExpandedOrderIds] = useState(() =>
    orders.length > 0 ? { [orders[0].id]: true } : {}
  );

  const toggleOrder = (orderId) => {
    setExpandedOrderIds((prev) => ({
      ...prev,
      [orderId]: !prev[orderId],
    }));
  };

  const totalVouchers = rewards?.total_vouchers || 0;
  const voucherBreakdown = rewards?.vouchers_by_manufacturer || {};
  const physicalRewards = Object.entries(rewards?.physical_rewards || {});
  const choiceSelections = rewards?.choice_selections || [];
  const manufacturerDetails = Object.entries(rewards?.details || {}).filter(
    ([, d]) => (d?.spending || 0) > 0 || (d?.gifts?.length || 0) > 0 || (d?.vouchers || 0) > 0
  );

  const hasAnyReward =
    totalVouchers > 0 ||
    physicalRewards.length > 0 ||
    choiceSelections.length > 0 ||
    manufacturerDetails.some(([, d]) => (d?.gifts?.length || 0) > 0);

  const activeOrdersCount = orders.filter((o) => o.status !== 'cancelled').length;

  const formatRewardSelectionList = (rawSelections) => {
    if (!rawSelections || typeof rawSelections !== 'object') return [];
    const hasInterbatSlot = Boolean(rawSelections.interbat_1 || rawSelections['Interbat #1']);
    const hasGrahaSlot = Boolean(rawSelections.graha_neo_1 || rawSelections['Neo Protifed #1']);
    const result = [];

    Object.entries(rawSelections).forEach(([key, val]) => {
      if (key === 'interbat' && hasInterbatSlot) return;
      if (key === 'graha_neo' && hasGrahaSlot) return;

      const labelMap = {
        interbat: 'Interbat',
        interbat_1: 'Interbat #1',
        interbat_2: 'Interbat #2',
        interbat_3: 'Interbat #3',
        graha_neo: 'Neo Protifed',
        graha_neo_1: 'Neo Protifed #1',
        graha_neo_2: 'Neo Protifed #2',
        graha_neo_3: 'Neo Protifed #3',
        exeltis_lanso: 'Exeltis Lansoprazole',
        exeltis_sucra: 'Exeltis Sucralfate',
      };

      const label = labelMap[key] || key.replace(/[_-]/g, ' ');
      if (Array.isArray(val)) {
        val.forEach((v, idx) => {
          if (typeof v === 'string' && v) {
            result.push({ label: `${label} #${idx + 1}`, value: v });
          }
        });
      } else if (typeof val === 'string' && val) {
        result.push({ label, value: val });
      }
    });

    return result;
  };

  return (
    <GuestLayout pharmacy={pharmacy} milestone={milestone} title="Pesanan & Hadiah Saya">
      <Head title="Riwayat Pesanan & Rincian Hadiah" />

      {/* Top Summary Banner */}
      <div className="bg-gradient-to-br from-brand-800 via-brand-700 to-brand-900 text-white p-4 mx-3.5 mt-3 rounded-2xl shadow-md relative overflow-hidden">
        <div className="absolute -right-6 -bottom-6 w-28 h-28 bg-amber-400/15 rounded-full blur-2xl pointer-events-none" />

        <div className="relative z-10 flex items-center justify-between gap-2 mb-3">
          <div>
            <span className="text-[10px] font-bold uppercase tracking-widest text-amber-300 flex items-center gap-1">
              <Sparkles className="w-3 h-3" />
              <span>REKAP BELANJA &amp; REWARD</span>
            </span>
            <h1 className="text-base font-extrabold tracking-tight text-white mt-0.5">
              Pesanan &amp; Hadiah Saya
            </h1>
          </div>
          <div className="text-right">
            <span className="text-[10px] text-brand-200 block">Total Akumulasi Sah</span>
            <span className="text-sm font-black text-amber-300">
              {formatRupiah(milestone?.total_spending || 0)}
            </span>
          </div>
        </div>

        <div className="relative z-10 grid grid-cols-3 gap-2 pt-2.5 border-t border-white/15 text-center">
          <div className="bg-white/10 rounded-xl py-2 px-1.5">
            <span className="text-[10px] text-brand-100 block">PO Sah</span>
            <span className="text-xs font-extrabold text-white">{activeOrdersCount} PO</span>
          </div>
          <div className="bg-white/10 rounded-xl py-2 px-1.5">
            <span className="text-[10px] text-brand-100 block">Total Voucher</span>
            <span className="text-xs font-extrabold text-amber-300">
              {formatRupiah(totalVouchers)}
            </span>
          </div>
          <div className="bg-white/10 rounded-xl py-2 px-1.5">
            <span className="text-[10px] text-brand-100 block">Hadiah Fisik</span>
            <span className="text-xs font-extrabold text-emerald-300">
              {physicalRewards.reduce((acc, [, q]) => acc + Number(q || 1), 0) +
                choiceSelections.length}{' '}
              Item
            </span>
          </div>
        </div>
      </div>

      {/*Segmented Control Tabs */}
      <div className="px-3.5 mt-3.5">
        <div className="bg-slate-200/80 p-1 rounded-xl grid grid-cols-2 gap-1">
          <button
            type="button"
            onClick={() => setActiveTab('rewards')}
            className={`py-2 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
              activeTab === 'rewards'
                ? 'bg-white text-brand-800 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            <Gift className="w-3.5 h-3.5 text-amber-500" />
            <span>Rincian Hadiah</span>
          </button>

          <button
            type="button"
            onClick={() => setActiveTab('orders')}
            className={`py-2 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
              activeTab === 'orders'
                ? 'bg-white text-brand-800 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            <ClipboardList className="w-3.5 h-3.5 text-brand-600" />
            <span>Riwayat PO ({orders.length})</span>
          </button>
        </div>
      </div>

      {/* TAB 1: RINCIAN HADIAH & ASAL PEROLEHAN */}
      {activeTab === 'rewards' && (
        <div className="p-3.5 space-y-3.5">
          {/* Handover Claim Status Banner */}
          {hasAnyReward && (
            <div
              className={`p-3.5 rounded-2xl border flex items-start gap-3 ${
                pharmacy?.is_reward_handed_over
                  ? 'bg-emerald-50 border-emerald-200 text-emerald-900'
                  : 'bg-amber-50/90 border-amber-200 text-amber-900'
              }`}
            >
              {pharmacy?.is_reward_handed_over ? (
                <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" />
              ) : (
                <Clock className="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
              )}
              <div className="text-xs leading-relaxed">
                <span className="font-bold block">
                  {pharmacy?.is_reward_handed_over
                    ? '✓ Hadiah & Voucher Telah Diserahkan'
                    : 'Status Klaim: Siap Diambil di Meja Logistik / Panitia'}
                </span>
                <span className="text-[11px] opacity-85">
                  {pharmacy?.is_reward_handed_over
                    ? `Diterima oleh ${pharmacy.reward_receiver_name || '-'} pada ${pharmacy.reward_handed_over_at || '-'}.`
                    : 'Tunjukkan halaman rincian ini kepada panitia saat sesi pengambilan hadiah & voucher.'}
                </span>
              </div>
            </div>
          )}

          {/* Voucher Breakdown Card */}
          <div className="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs">
            <div className="flex items-center justify-between mb-3 pb-2.5 border-b border-slate-100">
              <div className="flex items-center gap-2">
                <div className="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                  <Ticket className="w-4 h-4" />
                </div>
                <div>
                  <h2 className="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Rincian Voucher Belanja
                  </h2>
                  <span className="text-[10px] text-slate-500">
                    Akumulasi voucher dari pabrikan sponsor
                  </span>
                </div>
              </div>
              <span className="text-sm font-black text-emerald-600">
                {formatRupiah(totalVouchers)}
              </span>
            </div>

            {Object.keys(voucherBreakdown).length > 0 ? (
              <div className="space-y-2">
                {Object.entries(voucherBreakdown).map(([slug, amount]) => {
                  const detailName =
                    rewards?.details?.[slug]?.name || slug.replace(/-/g, ' ').toUpperCase();
                  return (
                    <div
                      key={slug}
                      className="flex items-center justify-between bg-emerald-50/60 border border-emerald-100 rounded-xl px-3 py-2 text-xs"
                    >
                      <span className="font-semibold text-slate-700">{detailName}</span>
                      <span className="font-bold text-emerald-700">{formatRupiah(amount)}</span>
                    </div>
                  );
                })}
              </div>
            ) : (
              <p className="text-xs text-slate-400 italic text-center py-2">
                Belum ada voucher terkumpul. Belanja paket promo untuk mendapatkan voucher.
              </p>
            )}
          </div>

          {/* Physical & Choice Gifts Card */}
          <div className="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs">
            <div className="flex items-center gap-2 mb-3 pb-2.5 border-b border-slate-100">
              <div className="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                <Gift className="w-4 h-4" />
              </div>
              <div>
                <h2 className="text-xs font-bold text-slate-900 uppercase tracking-wide">
                  Daftar Hadiah Fisik &amp; Pilihan
                </h2>
                <span className="text-[10px] text-slate-500">
                  Barang hadiah langsung &amp; hadiah pilihan yang terbuka
                </span>
              </div>
            </div>

            {physicalRewards.length > 0 || choiceSelections.length > 0 ? (
              <ul className="divide-y divide-slate-100">
                {physicalRewards.map(([giftName, qty]) => (
                  <li key={giftName} className="py-2.5 flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2.5 min-w-0">
                      <span className="text-base">🎁</span>
                      <div className="min-w-0">
                        <span className="text-xs font-bold text-slate-800 block truncate">
                          {giftName}
                        </span>
                        <span className="text-[10px] text-slate-500">Hadiah Langsung</span>
                      </div>
                    </div>
                    <span className="bg-amber-100 text-amber-800 border border-amber-200 text-[11px] font-bold px-2.5 py-0.5 rounded-full shrink-0">
                      {qty} pcs
                    </span>
                  </li>
                ))}

                {choiceSelections.map((cs, idx) => (
                  <li key={idx} className="py-2.5 flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2.5 min-w-0">
                      <span className="text-base">✨</span>
                      <div className="min-w-0">
                        <span className="text-xs font-bold text-slate-800 block truncate">
                          {cs.selected_option}
                        </span>
                        <span className="text-[10px] text-blue-600 font-semibold uppercase">
                          Pilihan Sponsor: {cs.manufacturer_slug}
                        </span>
                      </div>
                    </div>
                    <span className="bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0">
                      Terpilih
                    </span>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-xs text-slate-400 italic text-center py-2">
                Belum ada hadiah fisik yang terbuka dari pesanan saat ini.
              </p>
            )}
          </div>

          {/* Asal Perolehan Hadiah Per Pabrikan (Audit Breakdown) */}
          <div className="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs">
            <div className="flex items-center gap-2 mb-3 pb-2.5 border-b border-slate-100">
              <div className="w-7 h-7 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center">
                <Building2 className="w-4 h-4" />
              </div>
              <div>
                <h2 className="text-xs font-bold text-slate-900 uppercase tracking-wide">
                  Asal Perolehan per Pabrikan
                </h2>
                <span className="text-[10px] text-slate-500">
                  Rincian omset &amp; syarat hadiah dari masing-masing pabrik
                </span>
              </div>
            </div>

            {manufacturerDetails.length > 0 ? (
              <div className="space-y-2.5">
                {manufacturerDetails.map(([slug, detail]) => (
                  <div
                    key={slug}
                    className="rounded-xl border border-slate-200/90 bg-slate-50/70 p-3 space-y-2"
                  >
                    <div className="flex items-center justify-between gap-2">
                      <span className="text-xs font-bold text-brand-900 uppercase">
                        🏢 {detail.name}
                      </span>
                      <span className="text-xs font-extrabold text-slate-900">
                        {formatRupiah(detail.spending || 0)}
                      </span>
                    </div>

                    {detail.gifts && detail.gifts.length > 0 ? (
                      <div className="space-y-1 pt-1 border-t border-slate-200/60">
                        {detail.gifts.map((g, i) => (
                          <div
                            key={i}
                            className="text-[11px] font-semibold text-emerald-800 bg-emerald-50/90 border border-emerald-200/70 rounded-lg px-2.5 py-1.5 flex items-center gap-1.5"
                          >
                            <span>✓</span>
                            <span>{g}</span>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <p className="text-[11px] text-slate-500 italic pt-1 border-t border-slate-200/60">
                        Belum mencapai batas minimum tier hadiah pada pabrikan ini.
                      </p>
                    )}
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-6">
                <Package className="w-8 h-8 text-slate-300 mx-auto mb-2" />
                <p className="text-xs text-slate-500">
                  Belum ada riwayat belanja pabrikan. Yuk mulai pilih produk di Katalog!
                </p>
              </div>
            )}
          </div>
        </div>
      )}

      {/* TAB 2: RIWAYAT PO SAYA */}
      {activeTab === 'orders' && (
        <div className="p-3.5 space-y-3">
          {orders.length > 0 ? (
            orders.map((order) => {
              const isExpanded = Boolean(expandedOrderIds[order.id]);
              const isCancelled = order.status === 'cancelled';
              const orderRewardList = formatRewardSelectionList(order.reward_selections);

              return (
                <div
                  key={order.id}
                  className={`bg-white rounded-2xl border transition-all overflow-hidden shadow-xs ${
                    isCancelled ? 'border-rose-200 opacity-75' : 'border-slate-200/90'
                  }`}
                >
                  {/* Order Card Header (Clickable Accordion) */}
                  <button
                    type="button"
                    onClick={() => toggleOrder(order.id)}
                    className="w-full p-4 text-left flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors cursor-pointer"
                  >
                    <div className="min-w-0">
                      <div className="flex items-center gap-2 mb-1">
                        <span className="font-mono font-bold text-xs text-brand-700">
                          {order.order_number}
                        </span>
                        {isCancelled ? (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                            <XCircle className="w-3 h-3" />
                            <span>Dibatalkan</span>
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                            <CheckCircle2 className="w-3 h-3" />
                            <span>Terkonfirmasi</span>
                          </span>
                        )}
                      </div>
                      <span className="text-[11px] text-slate-500 block">
                        🕒 {order.created_date_formatted} &bull; {order.total_items} Qty Barang
                      </span>
                    </div>

                    <div className="flex items-center gap-2 shrink-0">
                      <div className="text-right">
                        <span className="text-xs font-black text-slate-900 block">
                          {formatRupiah(order.total_amount)}
                        </span>
                        <span className="text-[10px] text-brand-600 font-semibold">
                          {isExpanded ? 'Tutup Rincian' : 'Lihat Rincian'}
                        </span>
                      </div>
                      {isExpanded ? (
                        <ChevronUp className="w-4 h-4 text-slate-400" />
                      ) : (
                        <ChevronDown className="w-4 h-4 text-slate-400" />
                      )}
                    </div>
                  </button>

                  {/* Collapsible Order Items & Details */}
                  {isExpanded && (
                    <div className="px-4 pb-4 pt-2 border-t border-slate-100 bg-slate-50/50 space-y-3">
                      <div className="divide-y divide-slate-200/70 bg-white rounded-xl border border-slate-200/80 px-3">
                        {order.items.map((item) => (
                          <div
                            key={item.id}
                            className="py-2.5 flex items-start justify-between gap-2 text-xs"
                          >
                            <div className="min-w-0 pr-2">
                              <span className="text-[10px] font-bold text-brand-600 uppercase block">
                                {item.manufacturer_name}
                              </span>
                              <span className="font-bold text-slate-800 block truncate">
                                {item.product_name}
                              </span>
                              <span className="text-[11px] text-slate-500">
                                {formatRupiah(item.price)} &times; {item.quantity} {item.uom}
                              </span>
                            </div>
                            <span className="font-bold text-slate-900 shrink-0 pt-3">
                              {formatRupiah(item.subtotal)}
                            </span>
                          </div>
                        ))}
                      </div>

                      {/* Chosen Rewards in this Order */}
                      {orderRewardList.length > 0 && (
                        <div className="bg-amber-50/90 border border-amber-200 rounded-xl p-2.5 space-y-1">
                          <span className="text-[10px] font-bold text-amber-900 uppercase tracking-wider block">
                            🎁 Hadiah Pilihan pada PO Ini:
                          </span>
                          {orderRewardList.map((r, i) => (
                            <div
                              key={i}
                              className="flex items-center justify-between text-[11px] text-amber-950"
                            >
                              <span className="font-medium">{r.label}:</span>
                              <span className="font-bold">{r.value}</span>
                            </div>
                          ))}
                        </div>
                      )}

                      {/* Order Notes */}
                      {order.notes && (
                        <div className="text-[11px] text-slate-600 bg-white border border-slate-200 rounded-lg p-2.5 italic">
                          Catatan: &ldquo;{order.notes}&rdquo;
                        </div>
                      )}
                    </div>
                  )}
                </div>
              );
            })
          ) : (
            <div className="bg-white rounded-2xl border border-slate-200 p-8 text-center space-y-3">
              <div className="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                <ClipboardList className="w-6 h-6" />
              </div>
              <div>
                <h3 className="text-sm font-bold text-slate-800">Belum Ada Pesanan (PO)</h3>
                <p className="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                  Anda belum mengirimkan pesanan malam ini. Pilih produk di katalog untuk mulai mengumpulkan hadiah.
                </p>
              </div>
              <Link
                href="/katalog"
                className="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-xs transition-colors"
              >
                <ShoppingBag className="w-4 h-4" />
                <span>Buka Katalog Produk</span>
              </Link>
            </div>
          )}
        </div>
      )}
    </GuestLayout>
  );
}
