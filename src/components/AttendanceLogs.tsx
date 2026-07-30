import React, { useState } from 'react';
import { 
  FileSpreadsheet, 
  Download, 
  Search, 
  Filter, 
  PlusCircle, 
  Calendar, 
  Clock, 
  ShieldCheck, 
  Check,
  UserCheck
} from 'lucide-react';
import { AttendanceRecord, StaffMember, AttendanceAction } from '../types';

interface AttendanceLogsProps {
  logs: AttendanceRecord[];
  staffList: StaffMember[];
  onAddManualLog: (staffId: string, action: AttendanceAction, notes: string) => Promise<void>;
  onTriggerSync: () => Promise<void>;
}

export const AttendanceLogs: React.FC<AttendanceLogsProps> = ({
  logs,
  staffList,
  onAddManualLog,
  onTriggerSync
}) => {
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [actionFilter, setActionFilter] = useState<string>('ALL');
  const [methodFilter, setMethodFilter] = useState<string>('ALL');
  const [showManualModal, setShowManualModal] = useState<boolean>(false);

  // Manual Form State
  const [selectedStaffId, setSelectedStaffId] = useState<string>(staffList[0]?.id || '');
  const [manualAction, setManualAction] = useState<AttendanceAction>('CLOCK_IN');
  const [manualNotes, setManualNotes] = useState<string>('');
  const [submitting, setSubmitting] = useState<boolean>(false);

  const filteredLogs = logs.filter(log => {
    const matchesSearch = 
      log.staffName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      log.department.toLowerCase().includes(searchTerm.toLowerCase()) ||
      log.id.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesAction = actionFilter === 'ALL' || log.action === actionFilter;
    const matchesMethod = methodFilter === 'ALL' || log.method === methodFilter;

    return matchesSearch && matchesAction && matchesMethod;
  });

  const handleManualSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedStaffId) return;
    setSubmitting(true);
    await onAddManualLog(selectedStaffId, manualAction, manualNotes || 'Manual Admin Entry');
    setSubmitting(false);
    setShowManualModal(false);
    setManualNotes('');
  };

  const handleExportCSV = () => {
    const headers = ["Log ID", "Staff Name", "Department", "Action", "Timestamp", "Method", "Notes", "Synced To Google Sheets"];
    const rows = filteredLogs.map(l => [
      l.id,
      `"${l.staffName}"`,
      `"${l.department}"`,
      l.action,
      `"${new Date(l.timestamp).toLocaleString()}"`,
      l.method,
      `"${l.notes || ''}"`,
      l.syncedToGoogleSheets ? "YES" : "NO"
    ]);

    const csvContent = "data:text/csv;charset=utf-8," + [headers.join(","), ...rows.map(e => e.join(","))].join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `LC_Studio_Attendance_Logs_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="max-w-7xl mx-auto p-4 sm:p-6 space-y-6">
      {/* Header & Controls */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl font-extrabold tracking-tight flex items-center gap-2 text-zinc-900">
            <UserCheck className="w-5 h-5 text-emerald-600" />
            <span>Attendance Audit History Logs</span>
          </h2>
          <p className="text-xs text-zinc-500 mt-0.5">
            Immutable log of all clock-in, clock-out, and break events recorded by the system.
          </p>
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          <button
            onClick={() => setShowManualModal(true)}
            className="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 border border-zinc-200 rounded-xl text-xs font-bold text-zinc-800 flex items-center gap-1.5 cursor-pointer transition-colors"
          >
            <PlusCircle className="w-4 h-4 text-emerald-600" />
            <span>Manual Admin Entry</span>
          </button>

          <button
            onClick={onTriggerSync}
            className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-xl text-xs font-bold text-white flex items-center gap-1.5 cursor-pointer transition-colors shadow-xs"
          >
            <FileSpreadsheet className="w-4 h-4" />
            <span>Sync Google Sheets</span>
          </button>

          <button
            onClick={handleExportCSV}
            className="px-3.5 py-2 bg-zinc-900 hover:bg-zinc-800 rounded-xl text-xs font-bold text-white flex items-center gap-1.5 cursor-pointer transition-colors shadow-xs"
          >
            <Download className="w-4 h-4" />
            <span>Export CSV</span>
          </button>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-4 text-zinc-900 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3">
        <div className="relative w-full md:w-72">
          <Search className="w-4 h-4 text-zinc-400 absolute left-3 top-2.5" />
          <input
            type="text"
            placeholder="Search log ID, name, department..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-zinc-50 border border-zinc-200 rounded-xl pl-9 pr-3 py-2 text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-zinc-400"
          />
        </div>

        <div className="flex items-center gap-2 w-full md:w-auto overflow-x-auto">
          <select
            value={actionFilter}
            onChange={(e) => setActionFilter(e.target.value)}
            className="bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs text-zinc-800 focus:outline-none focus:border-zinc-400"
          >
            <option value="ALL">All Actions</option>
            <option value="CLOCK_IN">Clock In</option>
            <option value="CLOCK_OUT">Clock Out</option>
            <option value="BREAK_START">Start Break</option>
            <option value="BREAK_END">End Break</option>
          </select>

          <select
            value={methodFilter}
            onChange={(e) => setMethodFilter(e.target.value)}
            className="bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs text-zinc-800 focus:outline-none focus:border-zinc-400"
          >
            <option value="ALL">All Methods</option>
            <option value="PIN">PIN Keypad</option>
            <option value="QR_CODE">QR Badge</option>
            <option value="NFC">NFC Tap</option>
            <option value="ADMIN_MANUAL">Admin Manual</option>
          </select>
        </div>
      </div>

      {/* Logs Table */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs overflow-x-auto">
        <table className="w-full text-left text-xs">
          <thead className="bg-zinc-100/80 text-zinc-600 uppercase font-mono font-semibold tracking-wider border-b border-zinc-200">
            <tr>
              <th className="py-3 px-4">Log ID</th>
              <th className="py-3 px-4">Staff Member</th>
              <th className="py-3 px-4">Action</th>
              <th className="py-3 px-4">Timestamp</th>
              <th className="py-3 px-4">Method</th>
              <th className="py-3 px-4">Google Sheets Sync</th>
              <th className="py-3 px-4">Notes</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-zinc-200 bg-white">
            {filteredLogs.length === 0 ? (
              <tr>
                <td colSpan={7} className="py-8 text-center text-zinc-500">
                  No attendance records found.
                </td>
              </tr>
            ) : (
              filteredLogs.map((log) => (
                <tr key={log.id} className="hover:bg-zinc-50/80 transition-colors">
                  <td className="py-3 px-4 font-mono text-zinc-500 text-[11px] font-bold">
                    {log.id}
                  </td>

                  <td className="py-3 px-4">
                    <p className="font-bold text-zinc-900">{log.staffName}</p>
                    <p className="text-[10px] text-zinc-500">{log.department}</p>
                  </td>

                  <td className="py-3 px-4">
                    {log.action === 'CLOCK_IN' && (
                      <span className="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        CLOCK IN
                      </span>
                    )}
                    {log.action === 'CLOCK_OUT' && (
                      <span className="px-2.5 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-800 border border-red-200">
                        CLOCK OUT
                      </span>
                    )}
                    {log.action === 'BREAK_START' && (
                      <span className="px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        BREAK START
                      </span>
                    )}
                    {log.action === 'BREAK_END' && (
                      <span className="px-2.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                        BREAK END
                      </span>
                    )}
                  </td>

                  <td className="py-3 px-4 font-mono text-zinc-700">
                    {new Date(log.timestamp).toLocaleString()}
                  </td>

                  <td className="py-3 px-4 font-mono text-xs">
                    <span className="bg-zinc-100 px-2 py-0.5 rounded border border-zinc-200 text-zinc-700">
                      {log.method}
                    </span>
                  </td>

                  <td className="py-3 px-4">
                    {log.syncedToDb ? (
                      <span className="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold">
                        <Check className="w-3.5 h-3.5" /> DB Synced
                      </span>
                    ) : (
                      <span className="text-[11px] text-amber-700 font-semibold">Pending</span>
                    )}
                  </td>

                  <td className="py-3 px-4 text-zinc-500 text-xs">
                    {log.notes || '—'}
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Manual Admin Modal */}
      {showManualModal && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white border border-zinc-200 rounded-2xl max-w-md w-full p-6 text-zinc-900 shadow-xl space-y-4">
            <h3 className="text-lg font-bold flex items-center gap-2">
              <ShieldCheck className="w-5 h-5 text-emerald-600" />
              <span>Admin Manual Attendance Entry</span>
            </h3>

            <form onSubmit={handleManualSubmit} className="space-y-4 text-xs">
              <div>
                <label className="block text-zinc-700 font-semibold mb-1">Select Staff Member</label>
                <select
                  value={selectedStaffId}
                  onChange={(e) => setSelectedStaffId(e.target.value)}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-zinc-400"
                >
                  {staffList.map((s) => (
                    <option key={s.id} value={s.id}>{s.name} ({s.department})</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-zinc-700 font-semibold mb-1">Action Type</label>
                <select
                  value={manualAction}
                  onChange={(e) => setManualAction(e.target.value as AttendanceAction)}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-zinc-400"
                >
                  <option value="CLOCK_IN">Clock In</option>
                  <option value="CLOCK_OUT">Clock Out</option>
                  <option value="BREAK_START">Start Break</option>
                  <option value="BREAK_END">End Break</option>
                </select>
              </div>

              <div>
                <label className="block text-zinc-700 font-semibold mb-1">Reason / Admin Notes</label>
                <textarea
                  rows={3}
                  value={manualNotes}
                  onChange={(e) => setManualNotes(e.target.value)}
                  placeholder="e.g. Forgot PIN at front gate, verified by Security"
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-zinc-400"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowManualModal(false)}
                  className="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl text-zinc-700 font-semibold cursor-pointer transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={submitting}
                  className="px-4 py-2 bg-zinc-900 hover:bg-zinc-800 rounded-xl text-white font-bold cursor-pointer transition-colors shadow-xs"
                >
                  {submitting ? 'Saving...' : 'Record Event'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
