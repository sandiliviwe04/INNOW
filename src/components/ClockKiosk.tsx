import React, { useState } from 'react';
import { 
  KeyRound, 
  QrCode, 
  Radio, 
  CheckCircle2, 
  AlertCircle, 
  Clock, 
  LogIn, 
  LogOut, 
  Coffee, 
  Volume2, 
  VolumeX, 
  User,
  Sparkles,
  ShieldCheck
} from 'lucide-react';
import { StaffMember, AttendanceAction } from '../types';

interface ClockKioskProps {
  staffList: StaffMember[];
  onClockEvent: (staffId: string, action: AttendanceAction, method: 'PIN' | 'QR_CODE' | 'NFC') => Promise<{ success: boolean; message: string; staff?: StaffMember }>;
}

export const ClockKiosk: React.FC<ClockKioskProps> = ({ staffList, onClockEvent }) => {
  const [method, setMethod] = useState<'PIN' | 'QR_CODE' | 'NFC'>('PIN');
  const [action, setAction] = useState<AttendanceAction>('CLOCK_IN');
  const [pin, setPin] = useState<string>('');
  const [soundEnabled, setSoundEnabled] = useState<boolean>(true);
  const [loading, setLoading] = useState<boolean>(false);
  const [result, setResult] = useState<{
    success: boolean;
    message: string;
    staff?: StaffMember;
    timestamp?: string;
  } | null>(null);

  // Quick PIN button handler
  const handleKeyClick = (val: string) => {
    if (val === 'CLEAR') {
      setPin('');
      return;
    }
    if (val === 'BACK') {
      setPin(prev => prev.slice(0, -1));
      return;
    }
    if (pin.length < 6) {
      setPin(prev => prev + val);
    }
  };

  const playChime = (isSuccess: boolean) => {
    if (!soundEnabled) return;
    try {
      const ctx = new (window.AudioContext || (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      
      if (isSuccess) {
        osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
        osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.1); // E5
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        osc.start();
        osc.stop(ctx.currentTime + 0.3);
      } else {
        osc.frequency.setValueAtTime(220, ctx.currentTime); // A3
        osc.frequency.setValueAtTime(180, ctx.currentTime + 0.15);
        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
        osc.start();
        osc.stop(ctx.currentTime + 0.4);
      }
    } catch {
      // Audio context fallback silent
    }
  };

  const handleSubmit = async (overrideStaffId?: string) => {
    const targetId = overrideStaffId || pin;
    if (!targetId) return;

    setLoading(true);
    setResult(null);

    const res = await onClockEvent(targetId, action, method);
    playChime(res.success);

    setResult({
      success: res.success,
      message: res.message,
      staff: res.staff,
      timestamp: new Date().toLocaleTimeString()
    });

    setPin('');
    setLoading(false);

    // Auto dismiss result after 6 seconds
    setTimeout(() => {
      setResult(prev => prev?.timestamp === res.staff?.id ? null : prev);
    }, 6000);
  };

  return (
    <div className="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">
      {/* Kiosk Title & Mode Header */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center gap-2">
            <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" />
            <h2 className="text-xl font-extrabold tracking-tight text-zinc-900">Digital Clocking Station</h2>
          </div>
          <p className="text-xs text-zinc-500 mt-0.5">
            Select check-in method & action, then enter PIN or scan staff badge.
          </p>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={() => setSoundEnabled(!soundEnabled)}
            className={`p-2 rounded-xl text-xs font-medium flex items-center gap-1.5 transition-colors cursor-pointer border ${
              soundEnabled ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-zinc-100 text-zinc-500 border-zinc-200'
            }`}
            title="Toggle Audio Feedback"
          >
            {soundEnabled ? <Volume2 className="w-4 h-4" /> : <VolumeX className="w-4 h-4" />}
            <span className="hidden sm:inline">{soundEnabled ? 'Audio On' : 'Audio Muted'}</span>
          </button>

          <div className="bg-zinc-100 px-3 py-1.5 rounded-xl border border-zinc-200 text-xs font-mono text-zinc-700 flex items-center gap-2">
            <Clock className="w-3.5 h-3.5 text-zinc-500" />
            <span>Kiosk Terminal #01</span>
          </div>
        </div>
      </div>

      {/* Action Selector Buttons */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <button
          onClick={() => setAction('CLOCK_IN')}
          className={`p-4 rounded-xl font-bold flex flex-col items-center justify-center gap-2 transition-all cursor-pointer border ${
            action === 'CLOCK_IN'
              ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm scale-[1.02]'
              : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
          }`}
        >
          <LogIn className="w-6 h-6" />
          <span className="text-sm">CLOCK IN</span>
        </button>

        <button
          onClick={() => setAction('CLOCK_OUT')}
          className={`p-4 rounded-xl font-bold flex flex-col items-center justify-center gap-2 transition-all cursor-pointer border ${
            action === 'CLOCK_OUT'
              ? 'bg-red-600 text-white border-red-600 shadow-sm scale-[1.02]'
              : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
          }`}
        >
          <LogOut className="w-6 h-6" />
          <span className="text-sm">CLOCK OUT</span>
        </button>

        <button
          onClick={() => setAction('BREAK_START')}
          className={`p-4 rounded-xl font-bold flex flex-col items-center justify-center gap-2 transition-all cursor-pointer border ${
            action === 'BREAK_START'
              ? 'bg-amber-600 text-white border-amber-600 shadow-sm scale-[1.02]'
              : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
          }`}
        >
          <Coffee className="w-6 h-6" />
          <span className="text-sm">START BREAK</span>
        </button>

        <button
          onClick={() => setAction('BREAK_END')}
          className={`p-4 rounded-xl font-bold flex flex-col items-center justify-center gap-2 transition-all cursor-pointer border ${
            action === 'BREAK_END'
              ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm scale-[1.02]'
              : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
          }`}
        >
          <Sparkles className="w-6 h-6" />
          <span className="text-sm">END BREAK</span>
        </button>
      </div>

      {/* Main Kiosk Card */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Verification Method Tabs */}
        <div className="lg:col-span-2 bg-white border border-zinc-200 rounded-2xl p-6 shadow-xs space-y-6">
          <div className="flex border-b border-zinc-200 pb-3 gap-2">
            <button
              onClick={() => setMethod('PIN')}
              className={`flex-1 py-2.5 rounded-xl font-semibold text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer ${
                method === 'PIN' ? 'bg-zinc-900 text-white shadow-xs' : 'text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100'
              }`}
            >
              <KeyRound className="w-4 h-4 text-emerald-400" />
              <span>PIN Keypad</span>
            </button>
            <button
              onClick={() => setMethod('QR_CODE')}
              className={`flex-1 py-2.5 rounded-xl font-semibold text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer ${
                method === 'QR_CODE' ? 'bg-zinc-900 text-white shadow-xs' : 'text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100'
              }`}
            >
              <QrCode className="w-4 h-4 text-sky-400" />
              <span>QR Badge Scanner</span>
            </button>
            <button
              onClick={() => setMethod('NFC')}
              className={`flex-1 py-2.5 rounded-xl font-semibold text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer ${
                method === 'NFC' ? 'bg-zinc-900 text-white shadow-xs' : 'text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100'
              }`}
            >
              <Radio className="w-4 h-4 text-amber-400" />
              <span>NFC Tap</span>
            </button>
          </div>

          {/* METHOD 1: PIN KEYPAD */}
          {method === 'PIN' && (
            <div className="max-w-xs mx-auto space-y-4">
              {/* PIN Display */}
              <div className="bg-zinc-50 border border-zinc-200 rounded-xl py-3 px-4 text-center">
                <span className="text-[10px] text-zinc-500 uppercase tracking-wider font-mono block mb-1 font-semibold">
                  Enter 4-Digit Staff PIN
                </span>
                <div className="flex justify-center gap-2 my-1">
                  {[0, 1, 2, 3].map((i) => (
                    <div
                      key={i}
                      className={`w-10 h-12 rounded-lg border flex items-center justify-center text-xl font-mono font-extrabold transition-all ${
                        pin[i]
                          ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-xs'
                          : 'border-zinc-200 bg-white text-zinc-300'
                      }`}
                    >
                      {pin[i] ? '•' : ''}
                    </div>
                  ))}
                </div>
              </div>

              {/* Keypad Grid */}
              <div className="grid grid-cols-3 gap-2">
                {['1', '2', '3', '4', '5', '6', '7', '8', '9', 'CLEAR', '0', 'BACK'].map((val) => (
                  <button
                    key={val}
                    onClick={() => handleKeyClick(val)}
                    className={`py-3 rounded-xl font-bold font-mono transition-all cursor-pointer active:scale-95 text-sm ${
                      val === 'CLEAR'
                        ? 'bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs'
                        : val === 'BACK'
                        ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs'
                        : 'bg-zinc-100 hover:bg-zinc-200 text-zinc-800 border border-zinc-200'
                    }`}
                  >
                    {val}
                  </button>
                ))}
              </div>

              <button
                disabled={pin.length < 4 || loading}
                onClick={() => handleSubmit()}
                className={`w-full py-3.5 rounded-xl font-extrabold text-sm transition-all cursor-pointer shadow-xs flex items-center justify-center gap-2 ${
                  pin.length >= 4 && !loading
                    ? 'bg-zinc-900 hover:bg-zinc-800 text-white active:scale-95'
                    : 'bg-zinc-100 text-zinc-400 cursor-not-allowed border border-zinc-200'
                }`}
              >
                {loading ? (
                  <span>Processing...</span>
                ) : (
                  <>
                    <ShieldCheck className="w-5 h-5 text-emerald-400" />
                    <span>VERIFY & SUBMIT</span>
                  </>
                )}
              </button>
            </div>
          )}

          {/* METHOD 2: QR CODE SCANNER */}
          {method === 'QR_CODE' && (
            <div className="text-center space-y-4 py-4">
              <div className="relative w-64 h-64 mx-auto bg-zinc-50 border-2 border-dashed border-sky-400/80 rounded-2xl flex flex-col items-center justify-center p-4 overflow-hidden group">
                <div className="absolute inset-x-0 top-0 h-1 bg-sky-500 shadow-xs animate-[bounce_2s_infinite]" />
                <QrCode className="w-20 h-20 text-sky-600 mb-2" />
                <p className="text-xs text-zinc-700 font-medium">Position Staff QR Badge in Camera View</p>
                <p className="text-[11px] text-zinc-400 mt-1">Simulator active. Click below to select staff badge.</p>
              </div>

              <div className="pt-2">
                <p className="text-xs text-zinc-500 mb-2 font-semibold">Simulate Badge Scan for Testing:</p>
                <div className="grid grid-cols-2 gap-2 max-w-md mx-auto">
                  {staffList.slice(0, 4).map((s) => (
                    <button
                      key={s.id}
                      onClick={() => handleSubmit(s.qrCode)}
                      className="p-2.5 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-left flex items-center gap-2.5 cursor-pointer text-xs text-zinc-800 transition-colors"
                    >
                      <img src={s.avatarUrl} alt={s.name} className="w-7 h-7 rounded-full object-cover" />
                      <div className="truncate">
                        <p className="font-semibold text-zinc-900 truncate">{s.name}</p>
                        <p className="text-[10px] text-zinc-500 font-mono">{s.qrCode}</p>
                      </div>
                    </button>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* METHOD 3: NFC TOUCHLESS TAP */}
          {method === 'NFC' && (
            <div className="text-center space-y-4 py-6">
              <div className="w-48 h-48 mx-auto bg-zinc-50 border-2 border-amber-400/80 rounded-full flex flex-col items-center justify-center p-4 relative">
                <div className="w-36 h-36 rounded-full border border-amber-400/40 flex items-center justify-center animate-ping absolute opacity-30" />
                <Radio className="w-16 h-16 text-amber-600 mb-2 animate-pulse" />
                <span className="text-xs text-zinc-800 font-bold">Hold NFC Card Near Reader</span>
              </div>

              <div className="pt-2">
                <p className="text-xs text-zinc-500 mb-2 font-semibold">Tap NFC Card for Staff:</p>
                <div className="flex flex-wrap justify-center gap-2">
                  {staffList.slice(0, 5).map((s) => (
                    <button
                      key={s.id}
                      onClick={() => handleSubmit(s.id)}
                      className="px-3 py-2 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-xs font-medium text-zinc-800 cursor-pointer flex items-center gap-2"
                    >
                      <span className="w-2 h-2 rounded-full bg-amber-500" />
                      <span>{s.name} ({s.id})</span>
                    </button>
                  ))}
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Quick Demo Staff Shortcuts & Last Result Card */}
        <div className="space-y-6">
          {/* Result Alert Box */}
          {result && (
            <div
              className={`p-5 rounded-2xl border text-zinc-900 shadow-md transition-all animate-in fade-in slide-in-from-top-2 ${
                result.success
                  ? 'bg-emerald-50/90 border-emerald-300'
                  : 'bg-red-50/90 border-red-300'
              }`}
            >
              <div className="flex items-start gap-3">
                {result.success ? (
                  <CheckCircle2 className="w-6 h-6 text-emerald-600 shrink-0 mt-0.5" />
                ) : (
                  <AlertCircle className="w-6 h-6 text-red-600 shrink-0 mt-0.5" />
                )}
                <div className="space-y-1">
                  <h4 className="font-extrabold text-sm text-zinc-900">
                    {result.success ? 'Clock Event Recorded' : 'Verification Failed'}
                  </h4>
                  <p className="text-xs text-zinc-700">{result.message}</p>
                  {result.timestamp && (
                    <p className="text-[10px] text-zinc-500 font-mono">Time: {result.timestamp}</p>
                  )}
                </div>
              </div>

              {result.staff && (
                <div className="mt-4 pt-3 border-t border-zinc-200 flex items-center gap-3">
                  <img
                    src={result.staff.avatarUrl}
                    alt={result.staff.name}
                    className="w-10 h-10 rounded-full object-cover ring-2 ring-emerald-500/40"
                  />
                  <div>
                    <p className="font-bold text-xs text-zinc-900">{result.staff.name}</p>
                    <p className="text-[11px] text-zinc-500">{result.staff.department} • {result.staff.role}</p>
                    <span className="inline-block mt-1 px-2 py-0.5 bg-white border border-emerald-300 rounded text-[10px] font-mono font-semibold text-emerald-700">
                      Status: {result.staff.status}
                    </span>
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Staff Quick Test Directory */}
          <div className="bg-white border border-zinc-200 rounded-2xl p-5 shadow-xs space-y-3">
            <div className="flex items-center justify-between">
              <h3 className="text-xs font-bold text-zinc-700 uppercase tracking-wider flex items-center gap-1.5">
                <User className="w-4 h-4 text-zinc-900" />
                <span>Staff PIN Cheat Sheet</span>
              </h3>
              <span className="text-[10px] text-zinc-400 font-mono">For testing</span>
            </div>

            <p className="text-xs text-zinc-500">
              Click any staff member below to test instant clock-in:
            </p>

            <div className="space-y-2 max-h-[320px] overflow-y-auto pr-1">
              {staffList.map((s) => (
                <div
                  key={s.id}
                  onClick={() => {
                    setPin(s.pin);
                    setMethod('PIN');
                  }}
                  className="p-2.5 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl flex items-center justify-between cursor-pointer transition-all group"
                >
                  <div className="flex items-center gap-2.5">
                    <img src={s.avatarUrl} alt={s.name} className="w-8 h-8 rounded-full object-cover" />
                    <div>
                      <p className="text-xs font-bold text-zinc-800 group-hover:text-zinc-900">{s.name}</p>
                      <p className="text-[10px] text-zinc-500">{s.department}</p>
                    </div>
                  </div>
                  <div className="text-right">
                    <span className="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                      PIN: {s.pin}
                    </span>
                    <p className="text-[10px] text-zinc-400 mt-0.5">{s.status}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
