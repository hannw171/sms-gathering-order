import React from 'react';
import { Gift, X, Check, Sparkles } from 'lucide-react';

export default function RewardSelectionModal({
  isOpen,
  onClose,
  unlockedPrograms = [],
  selectedChoices = {},
  onSelectChoice,
}) {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div 
        className="w-full max-w-guest bg-white rounded-t-3xl sm:rounded-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200 animate-in slide-in-from-bottom-6 duration-200"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Modal Header */}
        <div className="bg-gradient-to-r from-brand-900 to-indigo-950 text-white p-4 px-5 flex items-center justify-between shrink-0">
          <div className="flex items-center gap-2.5">
            <div className="w-9 h-9 rounded-xl bg-amber-400 text-amber-950 flex items-center justify-center font-bold shadow-sm">
              <Gift className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-sm text-white flex items-center gap-1.5">
                <span>Pilih Hadiah Reward Sponsor</span>
                <Sparkles className="w-3.5 h-3.5 text-amber-300" />
              </h3>
              <p className="text-[11px] text-brand-200">
                {unlockedPrograms.length} Pilihan hadiah dapat Anda tentukan
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="p-1.5 rounded-full hover:bg-white/10 text-white/80 hover:text-white transition-colors"
            title="Tutup"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Modal Body: Scrollable list of programs */}
        <div className="p-4 overflow-y-auto space-y-4 flex-1 overscroll-contain">
          {unlockedPrograms.map((prog) => {
            const currentSelection = selectedChoices[prog.key];

            return (
              <div 
                key={prog.key} 
                className="bg-slate-50 border border-slate-200 rounded-2xl p-4 shadow-2xs space-y-2.5"
              >
                {/* Program Header */}
                <div className="flex items-start justify-between gap-2">
                  <div>
                    <span className="text-[10px] font-bold text-brand-700 uppercase tracking-wider block">
                      {prog.manufacturer || 'SPONSOR'}
                    </span>
                    <h4 className="font-bold text-sm text-slate-900">
                      {prog.title}
                    </h4>
                  </div>
                  <span className="text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 px-2 py-0.5 rounded-full shrink-0">
                    {prog.badge}
                  </span>
                </div>

                <p className="text-[11px] text-slate-600 leading-relaxed">
                  {prog.description}
                </p>

                {/* Options List */}
                <div className="grid grid-cols-1 gap-2 pt-1">
                  {prog.options.map((opt, optIdx) => {
                    const isSelected = currentSelection === opt;
                    const isHighest = optIdx === 0 && prog.options.length > 1;

                    return (
                      <label
                        key={opt}
                        onClick={() => onSelectChoice(prog.key, opt)}
                        className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all ${
                          isSelected
                            ? 'bg-blue-50/90 border-blue-500 font-bold text-blue-950 shadow-xs'
                            : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100/70'
                        }`}
                      >
                        <div className="flex items-center gap-2.5 min-w-0 pr-2">
                          <input
                            type="radio"
                            name={`modal_choice_${prog.key}`}
                            value={opt}
                            checked={isSelected}
                            onChange={() => onSelectChoice(prog.key, opt)}
                            className="w-4 h-4 text-blue-600 focus:ring-blue-500"
                          />
                          <span className="text-xs truncate">{opt}</span>
                        </div>

                        <div className="shrink-0 flex items-center gap-1">
                          {isHighest ? (
                            <span className="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                              Tier Tertinggi
                            </span>
                          ) : (
                            <span className="text-[10px] text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-full">
                              Pilihan
                            </span>
                          )}
                        </div>
                      </label>
                    );
                  })}
                </div>
              </div>
            );
          })}
        </div>

        {/* Modal Footer */}
        <div className="p-4 bg-white border-t border-slate-200 shrink-0">
          <button
            type="button"
            onClick={onClose}
            className="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold text-sm py-3 px-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]"
          >
            <Check className="w-4 h-4 stroke-[2.5]" />
            <span>Simpan Pilihan &amp; Lanjutkan ke Keranjang</span>
          </button>
        </div>
      </div>
    </div>
  );
}
