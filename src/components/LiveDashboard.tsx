import React, { useState } from 'react';
import { 
  Users, 
  UserCheck, 
  Coffee, 
  UserX, 
  Search, 
  Filter, 
  AlertTriangle, 
  Clock, 
  Building2, 
  ShieldCheck,
  ChevronRight,
  Download,
  CheckCircle2
} from 'lucide-react';
import { StaffMember, DepartmentSummary } from '../types';

interface LiveDashboardProps {
  staffList: StaffMember[];
  departmentSummaries: DepartmentSummary[];
  onSelectStaffBadge: (staff: StaffMember) => void;
}

export const LiveDashboard: React.FC<LiveDashboardProps> = ({
  staffList,
  departmentSummaries,
  onSelectStaffBadge
}) => {
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [selectedDept, setSelectedDept] = useState<string>('ALL');
  const [selectedStatus, setSelectedStatus] = useState<string>('ALL');

  const onsiteCount = staffList.filter(s => s.status === 'ONSITE').length;
  const breakCount = staffList.filter(s => s.status === 'BREAK').length;
  const offsiteCount = staffList.filter(s => s.status === 'OFFSITE').length;

  const filteredStaff = staffList.filter(staff => {
    const matchesSearch = 
      staff.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      staff.department.toLowerCase().includes(searchTerm.toLowerCase()) ||
      staff.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
      staff.id.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesDept = selectedDept === 'ALL' || staff.department === selectedDept;
    const matchesStatus = selectedStatus === 'ALL' || staff.status === selectedStatus;

    return matchesSearch && matchesDept && matchesStatus;
  });

  const formatDuration = (isoString?: string) => {
    if (!isoString) return 'N/A';
    const diff = Math.floor((Date.now() - new Date(isoString).getTime()) / 1000);
    if (diff < 0) return 'Just now';
    const hours = Math.floor(diff / 3600);
    const mins = Math.floor((diff % 3600) / 60);
    if (hours > 0) {
      return `${hours}h ${mins}m`;
    }
    return `${mins}m`;
  };

  return (
    <div className="max-w-7xl mx-auto p-4 sm:p-6 space-y-6">
      {/* Top Summary Metric Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs relative overflow-hidden">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Onsite Now</span>
            <span className="p-2 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
              <UserCheck className="w-5 h-5" />
            </span>
          </div>
          <div className="mt-3 flex items-baseline gap-2">
            <span className="text-3xl font-extrabold text-emerald-700">{onsiteCount}</span>
            <span className="text-xs text-zinc-500">/ {staffList.length} total staff</span>
          </div>
          <p className="text-[11px] text-emerald-700 mt-2 flex items-center gap-1 font-medium">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
            Verified inside building
          </p>
        </div>

        <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs relative overflow-hidden">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-zinc-500 uppercase tracking-wider">On Break</span>
            <span className="p-2 bg-amber-50 border border-amber-200 rounded-xl text-amber-700">
              <Coffee className="w-5 h-5" />
            </span>
          </div>
          <div className="mt-3 flex items-baseline gap-2">
            <span className="text-3xl font-extrabold text-amber-700">{breakCount}</span>
            <span className="text-xs text-zinc-500">staff members</span>
          </div>
          <p className="text-[11px] text-amber-700 mt-2 font-medium">
            Temporarily away from workstation
          </p>
        </div>

        <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs relative overflow-hidden">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Clocked Out</span>
            <span className="p-2 bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-600">
              <UserX className="w-5 h-5" />
            </span>
          </div>
          <div className="mt-3 flex items-baseline gap-2">
            <span className="text-3xl font-extrabold text-zinc-800">{offsiteCount}</span>
            <span className="text-xs text-zinc-500">staff offsite</span>
          </div>
          <p className="text-[11px] text-zinc-500 mt-2 font-medium">
            Not currently on premises
          </p>
        </div>

        <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between">
            <span className="text-xs font-extrabold text-red-700 uppercase tracking-wider flex items-center gap-1.5">
              <ShieldCheck className="w-4 h-4 text-red-600" />
              MySQL Sync
            </span>
            <span className="text-[10px] bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded border border-emerald-200 font-mono font-semibold">ONLINE</span>
          </div>
          <div>
            <h4 className="text-sm font-bold text-zinc-900 mt-1">Database Storage</h4>
            <p className="text-[11px] text-zinc-500 mt-0.5">Real-time attendance logs saved directly to database.</p>
          </div>
          <div className="mt-3 text-[11px] text-emerald-700 font-mono font-bold flex items-center gap-1">
            <CheckCircle2 className="w-3.5 h-3.5" />
            <span>Attendance Database Ready</span>
          </div>
        </div>
      </div>

      {/* Departmental Occupancy Breakdown */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs space-y-3">
        <h3 className="text-xs font-bold text-zinc-700 uppercase tracking-wider flex items-center gap-2">
          <Building2 className="w-4 h-4 text-emerald-600" />
          <span>Department Onsite Distribution</span>
        </h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
          {departmentSummaries.map((dept) => {
            const percentage = dept.total > 0 ? Math.round((dept.onsite / dept.total) * 100) : 0;
            return (
              <div key={dept.name} className="bg-zinc-50 p-3.5 rounded-xl border border-zinc-200 space-y-2">
                <div className="flex justify-between items-center text-xs">
                  <span className="font-semibold text-zinc-800 truncate">{dept.name}</span>
                  <span className="font-mono font-bold text-emerald-700">{dept.onsite}/{dept.total}</span>
                </div>
                <div className="w-full h-2 bg-zinc-200 rounded-full overflow-hidden">
                  <div
                    className="h-full bg-emerald-600 transition-all duration-500 rounded-full"
                    style={{ width: `${percentage}%` }}
                  />
                </div>
                <div className="flex justify-between text-[10px] text-zinc-500 font-mono">
                  <span>{percentage}% Onsite</span>
                  {dept.break > 0 && <span className="text-amber-700 font-semibold">{dept.break} on break</span>}
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Staff Filter Bar & Main Directory Table */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs space-y-4">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h3 className="text-lg font-bold text-zinc-900 flex items-center gap-2">
              <Users className="w-5 h-5 text-emerald-600" />
              <span>Live Staff Presence Directory</span>
            </h3>
            <p className="text-xs text-zinc-500">Real-time status updates from kiosk check-ins.</p>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            {/* Search Input */}
            <div className="relative min-w-[200px]">
              <Search className="w-4 h-4 text-zinc-400 absolute left-3 top-2.5" />
              <input
                type="text"
                placeholder="Search name, ID..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full bg-zinc-50 border border-zinc-200 rounded-xl pl-9 pr-3 py-2 text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-zinc-400"
              />
            </div>

            {/* Department Filter */}
            <select
              value={selectedDept}
              onChange={(e) => setSelectedDept(e.target.value)}
              className="bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs text-zinc-800 focus:outline-none focus:border-zinc-400"
            >
              <option value="ALL">All Departments</option>
              {departmentSummaries.map((d) => (
                <option key={d.name} value={d.name}>{d.name}</option>
              ))}
            </select>

            {/* Status Filter */}
            <select
              value={selectedStatus}
              onChange={(e) => setSelectedStatus(e.target.value)}
              className="bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs text-zinc-800 focus:outline-none focus:border-zinc-400"
            >
              <option value="ALL">All Statuses</option>
              <option value="ONSITE">Onsite Only</option>
              <option value="BREAK">On Break</option>
              <option value="OFFSITE">Clocked Out</option>
            </select>
          </div>
        </div>

        {/* Table View */}
        <div className="overflow-x-auto rounded-xl border border-zinc-200">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-100/80 text-zinc-600 uppercase font-mono font-semibold tracking-wider border-b border-zinc-200">
              <tr>
                <th className="py-3 px-4">Staff Member</th>
                <th className="py-3 px-4">Department & Role</th>
                <th className="py-3 px-4">Status</th>
                <th className="py-3 px-4">Last Clock In</th>
                <th className="py-3 px-4">Time Onsite</th>
                <th className="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 bg-white">
              {filteredStaff.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-8 text-center text-zinc-500">
                    No staff members match the selected criteria.
                  </td>
                </tr>
              ) : (
                filteredStaff.map((staff) => (
                  <tr key={staff.id} className="hover:bg-zinc-50/80 transition-colors">
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-3">
                        <img
                          src={staff.avatarUrl}
                          alt={staff.name}
                          className="w-9 h-9 rounded-full object-cover ring-2 ring-zinc-200"
                        />
                        <div>
                          <p className="font-bold text-zinc-900 text-sm">{staff.name}</p>
                          <p className="text-[10px] text-zinc-500 font-mono">{staff.id} • PIN: {staff.pin}</p>
                        </div>
                      </div>
                    </td>

                    <td className="py-3 px-4">
                      <p className="font-semibold text-zinc-800">{staff.department}</p>
                      <p className="text-[11px] text-zinc-500">{staff.role}</p>
                    </td>

                    <td className="py-3 px-4">
                      {staff.status === 'ONSITE' && (
                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                          <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                          ONSITE
                        </span>
                      )}
                      {staff.status === 'BREAK' && (
                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                          <span className="w-2 h-2 rounded-full bg-amber-500" />
                          ON BREAK
                        </span>
                      )}
                      {staff.status === 'OFFSITE' && (
                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-zinc-100 text-zinc-600 border border-zinc-200">
                          <span className="w-2 h-2 rounded-full bg-zinc-400" />
                          OFFSITE
                        </span>
                      )}
                    </td>

                    <td className="py-3 px-4 font-mono text-zinc-700">
                      {staff.lastClockIn ? new Date(staff.lastClockIn).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '—'}
                    </td>

                    <td className="py-3 px-4 font-mono text-emerald-700 font-bold">
                      {staff.status === 'ONSITE' ? formatDuration(staff.lastClockIn) : '—'}
                    </td>

                    <td className="py-3 px-4 text-right">
                      <button
                        onClick={() => onSelectStaffBadge(staff)}
                        className="px-2.5 py-1.5 bg-zinc-100 hover:bg-zinc-200 border border-zinc-200 rounded-lg text-xs font-semibold text-zinc-800 transition-colors cursor-pointer"
                      >
                        View Badge
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
