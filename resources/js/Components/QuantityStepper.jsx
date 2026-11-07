import React, { useState, useEffect } from 'react';
import { Minus, Plus } from 'lucide-react';

export default function QuantityStepper({
  value = 0,
  onChange,
  min = 0,
  max = 9999,
  step = 1,
  minOrder = 1,
  size = 'md',
  className = '',
}) {
  const [inputValue, setInputValue] = useState(String(value));

  // Sync internal state when external value changes
  useEffect(() => {
    setInputValue(String(value));
  }, [value]);

  const commitValue = (val) => {
    const parsed = parseInt(val, 10);
    if (isNaN(parsed) || parsed <= 0) {
      setInputValue(String(min));
      if (onChange) onChange(min);
      return;
    }

    let clamped = Math.min(max, Math.max(minOrder, parsed));
    if (step > 1) {
      clamped = Math.round(clamped / step) * step;
      if (clamped < minOrder) clamped = minOrder;
      if (clamped > max) clamped = max;
    }

    setInputValue(String(clamped));
    if (onChange) onChange(clamped);
  };

  const handleInputChange = (e) => {
    const raw = e.target.value;
    // Allow empty string temporarily while user is backspacing
    if (raw === '') {
      setInputValue('');
      return;
    }
    // Only accept numeric characters
    if (/^\d+$/.test(raw)) {
      setInputValue(raw);
      const parsed = parseInt(raw, 10);
      if (!isNaN(parsed) && onChange) {
        onChange(Math.min(max, parsed));
      }
    }
  };

  const handleBlur = () => {
    commitValue(inputValue);
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      e.target.blur();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      handleIncrement();
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      handleDecrement();
    }
  };

  const handleIncrement = () => {
    const current = parseInt(inputValue, 10) || 0;
    const stepVal = Math.max(1, parseInt(step, 10) || 1);
    const minVal = Math.max(stepVal, parseInt(minOrder, 10) || 1);

    let next;
    if (current === 0) {
      next = Math.min(max, minVal);
    } else {
      next = Math.min(max, current + stepVal);
    }
    commitValue(next);
  };

  const handleDecrement = () => {
    const current = parseInt(inputValue, 10) || 0;
    const stepVal = Math.max(1, parseInt(step, 10) || 1);
    const minVal = Math.max(stepVal, parseInt(minOrder, 10) || 1);

    let next;
    if (current - stepVal < minVal || current <= minVal) {
      next = min; // Return to 0
    } else {
      next = Math.max(min, current - stepVal);
    }
    commitValue(next);
  };

  const isSmall = size === 'sm';

  return (
    <div
      className={`inline-flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/80 transition-all focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 ${className}`}
    >
      {/* Minus Button */}
      <button
        type="button"
        onClick={handleDecrement}
        disabled={value <= min}
        className={`${
          isSmall ? 'w-6 h-6' : 'w-7 h-7'
        } rounded-lg bg-white flex items-center justify-center text-slate-700 hover:bg-slate-200 disabled:opacity-30 disabled:hover:bg-white shadow-xs transition-colors shrink-0`}
        aria-label="Kurangi kuantitas"
      >
        <Minus className={isSmall ? 'w-3 h-3' : 'w-3.5 h-3.5'} />
      </button>

      {/* Manual Numeric Typing Input */}
      <input
        type="text"
        inputMode="numeric"
        pattern="[0-9]*"
        value={inputValue}
        onChange={handleInputChange}
        onBlur={handleBlur}
        onKeyDown={handleKeyDown}
        onFocus={(e) => e.target.select()}
        aria-label="Jumlah kuantitas"
        className={`${
          isSmall ? 'w-10 text-xs py-0.5' : 'w-12 text-sm py-1'
        } text-center font-bold text-slate-900 bg-transparent border-none p-0 focus:ring-0 focus:outline-none select-all`}
      />

      {/* Plus Button */}
      <button
        type="button"
        onClick={handleIncrement}
        disabled={value >= max}
        className={`${
          isSmall ? 'w-6 h-6' : 'w-7 h-7'
        } rounded-lg bg-brand-600 flex items-center justify-center text-white hover:bg-brand-700 disabled:opacity-30 shadow-xs transition-colors shrink-0`}
        aria-label="Tambah kuantitas"
      >
        <Plus className={isSmall ? 'w-3 h-3' : 'w-3.5 h-3.5'} />
      </button>
    </div>
  );
}
