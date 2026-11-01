import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Building2, ArrowRight, ShieldAlert, Sparkles, CheckCircle2 } from 'lucide-react';

export default function Onboarding({ event, samplePharmacies, errors, conflict, pharmacy }) {
  const [showConflictModal, setShowConflictModal] = useState(!!conflict);
  const [selectedCode, setSelectedCode] = useState('');

  const { data, setData, post, processing } = useForm({
    customer_code: '',
    force_takeover: false,
  });

  const handleSubmit = (e) => {
    e?.preventDefault();
    post('/login-guest');
  };

  const handleQuickSelect = (code) => {
    setData('customer_code', code);
    setSelectedCode(code);
  };

  const handleTakeover = () => {
    setData('force_takeover', true);
    post('/login-guest');
  };

  return (
    <div className="min-h-screen bg-slate-900 flex justify-center items-center py-0 sm:py-6 selection:bg-brand-500 selection:text-white">
      <Head title="Identifikasi Tamu Gathering" />

      <div className="w-full max-w-guest min-h-screen bg-canvas text-slate-900 flex flex-col relative shadow-2xl overflow-hidden border-x border-slate-200 justify-between">
        {/* Top Header Background Banner */}
        <div className="bg-gradient-to-br from-brand-700 via-brand-600 to-brand-900 text-white p-6 pt-10 rounded-b-3xl shadow-md relative overflow-hidden">
          <div className="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none" />
          <div className="absolute -bottom-10 -left-10 w-36 h-36 bg-amber-400/10 rounded-full blur-xl pointer-events-none" />

          <div className="relative z-10 flex flex-col items-center text-center">
            <div className="w-14 h-14 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 mb-3 shadow-inner">
              <Building2 className="w-7 h-7 text-white" />
            </div>
            <span className="text-[11px] font-bold tracking-widest text-brand-100 uppercase bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10 mb-1.5">
              SMS GATHERING 2026
            </span>
            <h1 className="text-xl font-bold tracking-tight text-white mb-1">
              PBF Order Gathering Night
            </h1>
            <p className="text-xs text-brand-100/90 max-w-xs leading-relaxed">
              Silakan masukkan Kode Apotek atau Nomor Meja Anda untuk mulai memesan dan ikuti perebutan reward.
            </p>
          </div>
        </div>

        {/* Input Form Body */}
        <div className="flex-1 px-5 py-6 flex flex-col justify-center">
          <form onSubmit={handleSubmit} className="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm space-y-4">
            <div>
              <label htmlFor="customer_code" className="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">
                Kode Apotek / Nama Apotek
              </label>
              <div className="relative">
                <input
                  type="text"
                  id="customer_code"
                  placeholder="Contoh: APT-001 atau Kimia Mandiri"
                  value={data.customer_code}
                  onChange={(e) => setData('customer_code', e.target.value)}
                  className={`w-full text-[15px] font-medium px-4 py-3 rounded-xl border ${
                    errors?.customer_code ? 'border-red-500 ring-2 ring-red-100' : 'border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-100'
                  } transition-all outline-none`}
                  autoFocus
                />
              </div>
              {errors?.customer_code && (
                <p className="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                  <span>⚠️</span> {errors.customer_code}
                </p>
              )}
            </div>

            <button
              type="submit"
              disabled={processing || !data.customer_code}
              className="w-full bg-brand-600 hover:bg-brand-700 active:bg-brand-800 disabled:opacity-50 text-white font-semibold py-3.5 px-4 rounded-xl shadow-md flex items-center justify-center gap-2 transition-all text-[15px]"
            >
              <span>{processing ? 'Memverifikasi...' : 'Masuk ke Katalog Pesanan'}</span>
              <ArrowRight className="w-4 h-4" />
            </button>
          </form>

          {/* Quick Click Sample Pharmacies for Seamless Demo */}
          {samplePharmacies && samplePharmacies.length > 0 && (
            <div className="mt-6">
              <div className="flex items-center gap-1.5 text-xs font-semibold text-slate-500 mb-2.5">
                <Sparkles className="w-3.5 h-3.5 text-amber-500" />
                <span>Pilih Cepat untuk Simulasi Demo:</span>
              </div>
              <div className="grid grid-cols-1 gap-2">
                {samplePharmacies.map((sample) => (
                  <button
                    key={sample.customer_code}
                    type="button"
                    onClick={() => handleQuickSelect(sample.customer_code)}
                    className={`text-left p-2.5 rounded-xl border transition-all flex items-center justify-between text-xs ${
                      data.customer_code === sample.customer_code
                        ? 'bg-brand-50/80 border-brand-500 text-brand-900 font-semibold ring-1 ring-brand-400'
                        : 'bg-white border-slate-200/80 text-slate-700 hover:border-slate-300 hover:bg-slate-50'
                    }`}
                  >
                    <div>
                      <span className="font-semibold">{sample.name}</span>
                      <span className="text-[11px] text-slate-400 ml-2 font-mono">({sample.customer_code})</span>
                    </div>
                    {sample.table_number && (
                      <span className="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold">
                        {sample.table_number}
                      </span>
                    )}
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="p-4 text-center text-[11px] text-slate-400 border-t border-slate-200">
          PT SMS Medika Gathering &bullet; Didukung Sistem Order Real-time
        </div>

        {/* Device Conflict Modal (Figma node 1:77) */}
        {showConflictModal && (
          <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div className="bg-white rounded-2xl max-w-xs w-full p-5 shadow-2xl border border-slate-200 text-center animate-in fade-in zoom-in duration-150">
              <div className="w-12 h-12 rounded-full bg-amber-100 text-amber-600 mx-auto flex items-center justify-center mb-3">
                <ShieldAlert className="w-6 h-6" />
              </div>
              <h3 className="text-base font-bold text-slate-900 mb-1">
                Apotek Sedang Aktif di Perangkat Lain
              </h3>
              <p className="text-xs text-slate-500 mb-4 leading-relaxed">
                Akun apotek ini terdeteksi sedang membuka sesi pesanan di handphone lain. Apakah Anda ingin mengambil alih sesi?
              </p>
              <div className="flex flex-col gap-2">
                <button
                  type="button"
                  onClick={handleTakeover}
                  className="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition-colors shadow-sm"
                >
                  Ambil Alih Sesi di HP Ini
                </button>
                <button
                  type="button"
                  onClick={() => setShowConflictModal(false)}
                  className="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium py-2.5 px-4 rounded-xl text-xs transition-colors"
                >
                  Batal
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
