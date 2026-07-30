import React from 'react';
import { X, Printer, ShieldCheck, QrCode } from 'lucide-react';
import { StaffMember } from '../types';

interface BadgeModalProps {
  staff: StaffMember | null;
  onClose: () => void;
}

export const BadgeModal: React.FC<BadgeModalProps> = ({ staff, onClose }) => {
  if (!staff) return null;

  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white border border-zinc-200 rounded-2xl max-w-sm w-full p-6 text-zinc-900 shadow-xl relative space-y-6">
        <button
          onClick={onClose}
          className="absolute top-4 right-4 p-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-500 hover:text-zinc-900 transition-colors cursor-pointer"
        >
          <X className="w-4 h-4" />
        </button>

        {/* Printable Digital ID Badge Card */}
        <div id="printable-badge" className="bg-white border-2 border-zinc-900 rounded-2xl p-6 text-center space-y-4 shadow-xs relative overflow-hidden">
          <div className="bg-zinc-900 text-white text-[11px] font-extrabold uppercase tracking-widest py-1 px-3 -mx-6 -mt-6 mb-4 flex items-center justify-center gap-2">
            <ShieldCheck className="w-4 h-4 text-emerald-400" />
            <span>LC STUDIO • OFFICIAL ONSITE PASS</span>
          </div>

          <div className="relative w-24 h-24 mx-auto">
            <img
              src={staff.avatarUrl}
              alt={staff.name}
              className="w-24 h-24 rounded-2xl object-cover border-2 border-zinc-200 shadow-xs mx-auto"
            />
            <div className="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" />
          </div>

          <div>
            <h3 className="text-lg font-extrabold text-zinc-900 tracking-tight">{staff.name}</h3>
            <p className="text-xs font-bold text-zinc-700">{staff.role}</p>
            <p className="text-[11px] text-zinc-500">{staff.department}</p>
          </div>

          {/* QR Code Container */}
          <div className="bg-zinc-50 p-3 rounded-xl border border-zinc-200 max-w-[140px] mx-auto flex flex-col items-center justify-center">
            <QrCode className="w-24 h-24 text-zinc-900" />
            <span className="text-[9px] font-mono font-bold text-zinc-700 mt-1">{staff.qrCode}</span>
          </div>

          <div className="bg-zinc-50 p-2.5 rounded-xl border border-zinc-200 flex justify-between items-center text-xs font-mono">
            <span className="text-zinc-500">STAFF ID:</span>
            <span className="font-bold text-zinc-900">{staff.id}</span>
            <span className="text-zinc-500 ml-2">PIN:</span>
            <span className="font-bold text-emerald-700">{staff.pin}</span>
          </div>

          <p className="text-[9px] text-zinc-400 uppercase tracking-wider font-mono">
            Digital Attendance Tracking Pass • Property of LC Studio
          </p>
        </div>

        <button
          onClick={handlePrint}
          className="w-full py-3 bg-zinc-900 hover:bg-zinc-800 text-white font-bold rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer"
        >
          <Printer className="w-4 h-4" />
          <span>PRINT DIGITAL ID BADGE</span>
        </button>
      </div>
    </div>
  );
};
