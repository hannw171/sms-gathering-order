import React, { useState, useMemo, useEffect } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import {
  Building2,
  Search,
  X,
  ArrowRight,
  Sparkles,
  MapPin,
  AlertCircle,
  Clock,
  PhoneCall,
  UserCheck,
  ChevronRight,
} from 'lucide-react';
import DeviceConflictModal from '../../Components/DeviceConflictModal';

export default function Onboarding({ event, samplePharmacies }) {
  const { errors, flash, app } = usePage().props;
  const isDev = app?.env === 'local' || !app?.is_production;

  const [search, setSearch] = useState('');
  const [selectedPharmacy, setSelectedPharmacy] = useState(null);
  const [isDemoConflictModalOpen, setIsDemoConflictModalOpen] = useState(false);
  const [isDemoNotFoundOpen, setIsDemoNotFoundOpen] = useState(false);

  // Form for submission
  const { data, setData, post, processing } = useForm({
    customer_code: '',
    force_takeover: false,
  });

  // Check if conflict returned from server session
  const serverConflictData = flash?.conflictData;
  const hasServerConflict = !!flash?.conflict;

  // Check if not found returned from server session
  const isNotFound = isDemoNotFoundOpen || !!flash?.notFound || (errors?.customer_code && !hasServerConflict);
  const notFoundQuery = flash?.searchedQuery || search || 'Apotek Nusantara 99';

  // Filter pharmacies based on search
  const filteredPharmacies = useMemo(() => {
    if (!search || search.trim() === '') return samplePharmacies || [];
    const q = search.toLowerCase();
    return (samplePharmacies || []).filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        p.customer_code.toLowerCase().includes(q) ||
        (p.table_number && p.table_number.toLowerCase().includes(q))
    );
  }, [search, samplePharmacies]);

  // Suggestions for Data Not Found state (Figma 1:2)
  const suggestions = useMemo(() => {
    return flash?.suggestions || (samplePharmacies || []).slice(0, 2);
  }, [flash?.suggestions, samplePharmacies]);

  const handleSelectPharmacy = (p) => {
    setSelectedPharmacy(p);
    setData('customer_code', p.customer_code);
    setSearch(p.name);
    setIsDemoNotFoundOpen(false);
  };

  const handleClearSearch = () => {
    setSearch('');
    setSelectedPharmacy(null);
    setData('customer_code', '');
    setIsDemoNotFoundOpen(false);
  };

  const handleSubmit = (e) => {
    e?.preventDefault();
    if (!data.customer_code && !search) return;
    setData('force_takeover', false);
    post('/login-guest');
  };

  const handleTakeover = () => {
    setData('force_takeover', true);
    post('/login-guest', {
      onFinish: () => {
        setIsDemoConflictModalOpen(false);
      },
    });
  };

  // Conflict modal state: either real from backend flash or simulated via demo button
  const activeConflict =
    (hasServerConflict ? serverConflictData : null) ||
    (isDemoConflictModalOpen
      ? {
          pharmacy_id: selectedPharmacy?.id || 1,
          pharmacy_name: selectedPharmacy?.name || 'Apotek Kimia Mandiri',
          previous_device: 'Chrome Mobile • Samsung S23',
          last_active_time: 'Terakhir aktif 2 menit lalu',
        }
      : null);

  return (
    <div className="min-h-screen bg-slate-900 flex justify-center items-center py-0 sm:py-6 selection:bg-brand-500 selection:text-white">
      <Head title="Identifikasi Tamu - SMS Gathering 2026" />

      {/* Mobile-first Viewport (matches Figma 390-448px) */}
      <div className="w-full max-w-guest min-h-screen bg-[#FAF8FF] text-slate-900 flex flex-col relative shadow-2xl overflow-hidden border-x border-slate-200 justify-between">
        
        {/* Main Content Area */}
        <div className={`p-4 transition-all duration-200 ${activeConflict ? 'blur-[1px] opacity-40 pointer-events-none' : ''}`}>
          
          {/* Header Context (Figma node 1:81, 1:942, 1:4) */}
          <header className="pt-2 pb-4 text-center sm:text-left">
            <div className="flex items-center justify-between mb-3">
              <div className="inline-flex items-center gap-1.5 bg-[#E2E7FF] text-[#00468C] px-3 py-1 rounded-full text-[11px] font-medium tracking-[0.22px]">
                <Building2 className="w-3.5 h-3.5 text-[#00468C]" />
                <span>PBF Sehat Medika Bersama</span>
              </div>
              <div className="inline-flex items-center gap-1 bg-[#FEF3C7] text-[#131B2E] px-2.5 py-0.5 rounded-full text-[11px] font-semibold tracking-[0.22px]">
                <span>🏆</span>
                <span>Gathering 2026</span>
              </div>
            </div>

            <h1 className="text-[22px] font-bold text-[#131B2E] tracking-[-0.33px] leading-[30px] mb-1">
              Annual Partner Gathering 2026
            </h1>
            <p className="text-[14px] text-[#565E74] leading-[20px] mb-2.5">
              PBF Sehat Medika Bersama
            </p>

            <div className="inline-flex items-center gap-1.5 bg-[#E2E7FF] text-[#1565C0] rounded-full px-3 py-1.5 text-[12px] font-semibold tracking-[0.12px]">
              <MapPin className="w-3.5 h-3.5 text-[#1565C0] shrink-0" />
              <span>Ballroom Grand Hotel &bull; Meja Undangan</span>
            </div>
          </header>

          {/* Session Terminated / Taken Over Notice */}
          {flash?.sessionTerminated && (
            <div className="bg-amber-50 border border-amber-200 rounded-[14px] p-3.5 mb-4 flex items-start gap-3 shadow-sm">
              <div className="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                <AlertCircle className="w-4 h-4" />
              </div>
              <div className="flex-1">
                <h4 className="text-[13px] font-bold text-amber-900 mb-0.5">
                  Sesi Dialihkan ke Perangkat Lain
                </h4>
                <p className="text-[12px] text-amber-800 leading-[18px]">
                  {flash?.terminatedMessage || 'Sesi apotek Anda telah dibuka di perangkat lain. Silakan pilih kembali apotek Anda jika ingin menggunakan perangkat ini.'}
                </p>
              </div>
            </div>
          )}

          {/* ========================================================================= */}
          {/* CASE 1: FIGMA NODE 1:2 (Onboarding - Data Not Found State)                */}
          {/* ========================================================================= */}
          {isNotFound ? (
            <div className="bg-white rounded-[16px] p-5 border border-slate-200/90 shadow-sm mb-4 space-y-3" data-node-id="1:2" data-name="Onboarding - Data Not Found">
              <div className="flex items-center justify-between">
                <span className="text-[14px] font-semibold text-[#131B2E]">
                  Cari Nama Apotek / Klinik Anda
                </span>
                <div className="flex items-center gap-1 text-[12px] font-semibold text-[#DC2626]">
                  <span className="w-2 h-2 rounded-full bg-[#DC2626]" />
                  <span>Tidak Ditemukan</span>
                </div>
              </div>

              {/* High-Contrast Error State Input (Figma node 1:29) */}
              <div className="bg-[#FEF2F2] border border-[#FECACA] rounded-[8px] p-[3px]">
                <div className="bg-white rounded-[8px] relative flex items-center">
                  <Search className="w-4 h-4 absolute left-3.5 text-slate-400" />
                  <input
                    type="text"
                    value={search || notFoundQuery}
                    onChange={(e) => {
                      setSearch(e.target.value);
                      setData('customer_code', e.target.value);
                    }}
                    className="w-full pl-10 pr-9 py-2.5 rounded-[8px] text-[14px] font-medium text-[#131B2E] outline-none border border-slate-300 focus:border-red-500"
                  />
                  <button
                    type="button"
                    onClick={handleClearSearch}
                    className="absolute right-2.5 text-slate-400 hover:text-slate-600 p-1"
                    title="Hapus pencarian"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </div>
              </div>

              {/* Inline Contextual Alert Box (Figma node 1:38) */}
              <div className="bg-[#FEF2F2] border border-[#FECACA]/60 rounded-[8px] p-3.5 flex items-start gap-3">
                <div className="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                  <AlertCircle className="w-4 h-4" />
                </div>
                <div className="flex-1">
                  <h4 className="text-[13px] font-semibold text-[#DC2626] mb-0.5">
                    Nama Sarana Tidak Ditemukan
                  </h4>
                  <p className="text-[12px] text-[#424752] leading-[18px]">
                    Pastikan nama sesuai undangan fisik atau hubungi PIC Sales meja Anda untuk aktivasi instan.
                  </p>
                  <button
                    type="button"
                    onClick={() => alert('Silakan menuju Meja Registrasi di Foyer Ballroom atau hubungi panitia PIC meja Anda.')}
                    className="mt-2 text-[12px] font-semibold text-[#1565C0] hover:underline flex items-center gap-1"
                  >
                    <span>Hubungi Bantuan Registrasi</span>
                    <ChevronRight className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              {/* Quick Auto-suggestion Chips for Self-Recovery (Figma node 1:51) */}
              <div className="pt-2">
                <span className="text-[11px] text-[#565E74] block mb-2 font-medium">
                  Saran nama serupa di Ballroom:
                </span>
                <div className="flex flex-col gap-1.5">
                  {suggestions.map((sug) => (
                    <button
                      key={sug.customer_code}
                      type="button"
                      onClick={() => handleSelectPharmacy(sug)}
                      className="text-left bg-[#F1F5F9] hover:bg-[#E2E7FF] text-[#131B2E] text-[12px] font-medium px-3 py-2 rounded-[8px] transition-colors flex items-center justify-between"
                    >
                      <span>&bull; {sug.name}</span>
                      <span className="text-[10px] text-slate-500 font-medium">
                        {event?.show_table_number && sug.table_number ? sug.table_number : sug.city || ''}
                      </span>
                    </button>
                  ))}
                </div>
              </div>

              {/* Disabled CTA Button (Figma node 1:59) */}
              <div className="pt-2">
                <button
                  type="button"
                  disabled
                  className="w-full h-[48px] bg-[#F1F5F9] text-[#94A3B8] font-semibold text-[14px] rounded-[12px] flex items-center justify-center gap-2 cursor-not-allowed"
                >
                  <span>Masuk & Mulai Belanja</span>
                  <ArrowRight className="w-4 h-4 opacity-50" />
                </button>
              </div>
            </div>
          ) : (
            /* ========================================================================= */
            /* CASE 2: NORMAL IDENTIFIKASI TAMU VIEW (Figma node 1:940)                  */
            /* ========================================================================= */
            <div className="bg-white rounded-[16px] p-5 border border-slate-200/90 shadow-sm mb-4">
              <div className="flex items-center justify-between mb-2">
                <label htmlFor="pharmacy_search_box" className="text-[14px] font-semibold text-[#131B2E]">
                  Cari Nama Apotek / Klinik Anda
                </label>
                <span className="text-[11px] font-semibold text-[#1565C0] flex items-center gap-1">
                  <Sparkles className="w-3.5 h-3.5 text-[#1565C0]" />
                  <span>Live Lookup</span>
                </span>
              </div>

              {/* Autocomplete Search Input */}
              <div className="relative mb-3">
                <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <input
                  type="text"
                  id="pharmacy_search_box"
                  placeholder="Ketik nama apotek atau kode undangan..."
                  value={search}
                  onChange={(e) => {
                    setSearch(e.target.value);
                    setData('customer_code', e.target.value);
                  }}
                  className="w-full pl-10 pr-9 py-2.5 bg-[#F1F5F9] rounded-[12px] border border-transparent text-[14px] font-semibold text-[#131B2E] placeholder-slate-400 focus:ring-2 focus:ring-brand-600 focus:bg-white transition-all outline-none"
                />
                {search && (
                  <button
                    type="button"
                    onClick={handleClearSearch}
                    className="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1"
                  >
                    <X className="w-3.5 h-3.5" />
                  </button>
                )}
              </div>

              {/* Suggestions Dropdown (Figma node 1:979) */}
              {search && !selectedPharmacy && filteredPharmacies.length > 0 && (
                <div className="bg-[#F1F5F9] rounded-[12px] border border-slate-200 overflow-hidden divide-y divide-slate-200 max-h-48 overflow-y-auto shadow-sm mb-3">
                  {filteredPharmacies.map((p) => (
                    <button
                      key={p.customer_code}
                      type="button"
                      onClick={() => handleSelectPharmacy(p)}
                      className="w-full text-left p-3 hover:bg-[#E3F2FD] transition-colors flex items-center justify-between"
                    >
                      <div>
                        <h4 className="text-xs font-bold text-[#131B2E]">{p.name}</h4>
                        <p className="text-[11px] text-slate-500">
                          {p.city || 'Wilayah Banyumas & Sekitarnya'}
                        </p>
                      </div>
                      <span className="text-[10px] font-bold text-brand-600 bg-white px-2 py-0.5 rounded border border-slate-200">
                        PILIH
                      </span>
                    </button>
                  ))}
                </div>
              )}

              {/* Partner Profile Card Pre-selected (Figma node 1:103) */}
              {selectedPharmacy && (
                <div className="bg-[#FAF8FF] rounded-[12px] p-3.5 border border-brand-200/80 mb-4 animate-in fade-in duration-150 space-y-2.5">
                  <div className="flex items-center justify-between">
                    <span className="text-[11px] font-semibold uppercase tracking-[0.55px] text-[#64748B]">
                      MITRA TERPILIH
                    </span>
                    <span className="bg-[#ECFDF5] text-[#059669] text-[11px] font-semibold px-2 py-0.5 rounded-full">
                      Terdaftar
                    </span>
                  </div>

                  <div className="flex items-center gap-3">
                    <div className="w-11 h-11 rounded-[10px] bg-[#E2E7FF] text-[#1565C0] flex items-center justify-center shrink-0">
                      <Building2 className="w-5 h-5" />
                    </div>
                    <div className="min-w-0 flex-1">
                      <h3 className="text-[14px] font-bold text-[#131B2E] truncate">
                        {selectedPharmacy.name}
                      </h3>
                      <p className="text-[11px] text-[#64748B] truncate">
                        {selectedPharmacy.city || 'Wilayah Jawa Tengah'}
                      </p>
                    </div>
                  </div>

                  <div className="bg-[#F2F3FF] rounded-[8px] p-2 flex items-center justify-between text-[11px]">
                    <div className="flex items-center gap-1.5 text-[#131B2E] font-medium">
                      <UserCheck className="w-3.5 h-3.5 text-[#1565C0]" />
                      <span>{selectedPharmacy.owner_name || 'Apoteker / PIC Pengelola'}</span>
                    </div>
                    <span className="text-[#059669] font-semibold flex items-center gap-1">
                      <span>✓ Siap Masuk</span>
                    </span>
                  </div>
                </div>
              )}

              {/* Primary Action Button (Figma node 1:145) */}
              <button
                type="button"
                onClick={handleSubmit}
                disabled={processing || (!data.customer_code && !search)}
                className="w-full h-[48px] bg-[#1565C0] hover:bg-[#0D47A1] active:bg-[#0B1C30] disabled:opacity-40 text-white font-semibold text-[14px] rounded-[12px] shadow-sm flex items-center justify-center gap-2 transition-all"
              >
                <span>{processing ? 'Memverifikasi...' : 'Lanjutkan Masuk Katalog Gathering'}</span>
                <ArrowRight className="w-4 h-4" />
              </button>
            </div>
          )}

          {/* Microcopy Helper (Figma node 1:64) */}
          <div className="text-center px-4 my-2">
            <p className="text-[11px] text-[#565E74]">
              Perlu bantuan? Hubungi panitia registrasi di lobi ballroom.
            </p>
          </div>

          {/* Helpful On-ground Support Banner (Figma node 1:67 - Gold/Amber theme) */}
          <div className="bg-[rgba(254,243,199,0.7)] border border-[rgba(253,230,138,0.8)] rounded-[12px] p-3.5 flex items-center gap-3 shadow-xs mb-4">
            <div className="w-9 h-9 rounded-full bg-[rgba(252,211,77,0.6)] flex items-center justify-center text-amber-900 shrink-0">
              <Clock className="w-4 h-4" />
            </div>
            <div className="flex-1 min-w-0">
              <h4 className="text-[13px] font-semibold text-[#131B2E]">
                Meja Registrasi Buka: 08:30 – 13:00 WIB
              </h4>
              <p className="text-[11px] text-[#424752] leading-[16px]">
                PIC Meja Bantuan siap melayani pencarian manual ID di Area Foyer Ballroom.
              </p>
            </div>
          </div>

          {/* Interactive Screen State Switchers for Instant Review (Only visible in Local / Development) */}
          {isDev && (
            <div className="mt-4 pt-3 border-t border-slate-200 flex flex-wrap items-center justify-center gap-2">
              <button
                type="button"
                onClick={() => setIsDemoConflictModalOpen(true)}
                className="text-[11px] font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-full transition-colors flex items-center gap-1"
              >
                <span>⚡</span>
                <span>Uji Screen Device Conflict Modal (Figma 1:77)</span>
              </button>
              <button
                type="button"
                onClick={() => setIsDemoNotFoundOpen(!isDemoNotFoundOpen)}
                className="text-[11px] font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-full transition-colors flex items-center gap-1"
              >
                <span>🔍</span>
                <span>{isDemoNotFoundOpen ? 'Kembali ke Normal' : 'Uji Screen Data Not Found (Figma 1:2)'}</span>
              </button>
            </div>
          )}
        </div>

        {/* Footer (Figma node 1:150) */}
        <footer className="p-4 text-center border-t border-slate-200">
          <p className="text-[11px] text-[#64748B]">
            Data sesi dilindungi protokol enkripsi PBF Sehat Medika Bersama.
          </p>
        </footer>

        {/* ========================================================================= */}
        {/* FIGMA NODE 1:77 - DEVICE CONFLICT MODAL OVERLAY                           */}
        {/* ========================================================================= */}
        <DeviceConflictModal
          isOpen={!!activeConflict}
          pharmacyName={activeConflict?.pharmacy_name || selectedPharmacy?.name || 'Apotek Kimia Mandiri'}
          previousDevice={activeConflict?.previous_device || 'Chrome Mobile • Samsung S23'}
          lastActiveTime={activeConflict?.last_active_time || 'Terakhir aktif 2 menit lalu'}
          onConfirm={handleTakeover}
          onCancel={() => {
            setIsDemoConflictModalOpen(false);
          }}
          isProcessing={processing}
        />
      </div>
    </div>
  );
}
