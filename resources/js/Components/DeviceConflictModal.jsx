import React from 'react';
import { ShieldAlert, Smartphone, AlertTriangle, ArrowRightLeft } from 'lucide-react';

export default function DeviceConflictModal({
  isOpen,
  pharmacyName = 'Apotek Kimia Mandiri',
  previousDevice = 'Chrome Mobile • Samsung S23',
  lastActiveTime = 'Terakhir aktif 2 menit lalu',
  onConfirm,
  onCancel,
  isProcessing = false,
}) {
  if (!isOpen) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#090D16]/65 backdrop-blur-[2px] animate-in fade-in duration-200"
      data-node-id="1:77"
      data-name="Onboarding - Device Conflict Modal"
      onClick={(e) => {
        if (e.target === e.currentTarget && !isProcessing) {
          onCancel?.();
        }
      }}
    >
      {/* Modal Card Container (Figma node 1:153) */}
      <div className="relative w-full max-w-[320px] bg-white rounded-[16px] p-5 shadow-[0px_25px_50px_-12px_rgba(0,0,0,0.25)] border border-slate-100 flex flex-col items-center text-center animate-in zoom-in-95 duration-150 overflow-hidden">
        
        {/* Ambient Glow Behind Icon (Figma node 1:184) */}
        <div className="absolute top-[-16px] left-1/2 -translate-x-1/2 w-[112px] h-[112px] bg-[#FCD34D]/25 blur-[14px] rounded-full pointer-events-none" />

        {/* Shield Alert Icon Chip (Figma node 1:186) */}
        <div className="relative z-10 w-14 h-14 rounded-full bg-[#FEF3C7] text-[#D97706] flex items-center justify-center shadow-xs mb-3 ring-4 ring-amber-100/50">
          <ShieldAlert className="w-7 h-7 stroke-[2.2]" />
        </div>

        {/* Title (Figma node 1:155) */}
        <h3 className="relative z-10 text-[18px] font-semibold text-[#131B2E] tracking-[-0.18px] mb-1.5">
          Sesi Sudah Aktif
        </h3>

        {/* Description Copy (Figma node 1:158) */}
        <p className="relative z-10 text-[12px] text-[#64748B] leading-[19.5px] mb-3.5 px-1">
          <span className="font-semibold text-[#131B2E]">{pharmacyName}</span> saat ini sedang login di perangkat lain. Ambil alih sesi ke perangkat ini?
        </p>

        {/* Active Device Metadata Pill (Figma node 1:160) */}
        <div className="relative z-10 w-full bg-[#F1F5F9] rounded-[12px] p-2.5 px-3 flex flex-col items-center gap-1 mb-3">
          <div className="flex items-center gap-1.5 text-[11px] font-semibold text-[#424752]">
            <Smartphone className="w-3.5 h-3.5 text-slate-500" />
            <span>Perangkat Aktif Sebelumnya</span>
          </div>
          <span className="text-[12px] font-medium text-[#131B2E] truncate max-w-full">
            {previousDevice}
          </span>
          <div className="flex items-center gap-1.5 text-[11px] text-[#64748B]">
            <span className="w-1.5 h-1.5 rounded-full bg-[#F59E0B]" />
            <span>{lastActiveTime}</span>
          </div>
        </div>

        {/* Warning Note Badge (Figma node 1:173) */}
        <div className="relative z-10 w-full bg-[#FFDAD6]/40 border border-[#FECACA]/60 rounded-[6px] px-2.5 py-1.5 flex items-center justify-center gap-1.5 text-[10px] text-[#93000A] font-medium mb-4">
          <AlertTriangle className="w-3.5 h-3.5 shrink-0 text-[#DC2626]" />
          <span>Sesi di perangkat lain akan otomatis dinonaktifkan.</span>
        </div>

        {/* Action Buttons Row (Figma node 1:190) */}
        <div className="relative z-10 w-full flex items-center gap-2.5 mb-3.5">
          <button
            type="button"
            onClick={onCancel}
            disabled={isProcessing}
            className="flex-1 h-[44px] bg-[#F1F5F9] hover:bg-slate-200 text-[#64748B] font-semibold text-[14px] rounded-[12px] transition-colors"
          >
            Batal
          </button>
          <button
            type="button"
            onClick={onConfirm}
            disabled={isProcessing}
            className="flex-1 h-[44px] bg-[#1565C0] hover:bg-[#0D47A1] text-white font-semibold text-[14px] rounded-[12px] shadow-[0px_4px_6px_-1px_rgba(21,101,192,0.25),0px_2px_4px_-2px_rgba(21,101,192,0.25)] flex items-center justify-center gap-1.5 transition-all"
          >
            <ArrowRightLeft className="w-4 h-4" />
            <span>{isProcessing ? 'Memproses...' : 'Ya, Ambil Alih'}</span>
          </button>
        </div>

        {/* Security Help Link (Figma node 1:179) */}
        <button
          type="button"
          onClick={() => alert('Silakan hubungi panitia registrasi di meja depan untuk mereset sesi apotek Anda.')}
          className="relative z-10 text-[11px] text-[#64748B] hover:text-[#131B2E] underline decoration-dotted decoration-1 tracking-[0.22px] transition-colors"
        >
          Bukan Anda? Lapor ke Panitia Meja Registrasi
        </button>
      </div>
    </div>
  );
}
