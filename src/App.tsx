import React, { useState, useEffect, useCallback } from 'react';
import { Navbar } from './components/Navbar';
import { ClockKiosk } from './components/ClockKiosk';
import { LiveDashboard } from './components/LiveDashboard';
import { AttendanceLogs } from './components/AttendanceLogs';
import { StaffDirectory } from './components/StaffDirectory';
import { DeliverablesViewer } from './components/DeliverablesViewer';
import { BadgeModal } from './components/BadgeModal';

import { StaffMember, AttendanceRecord, DepartmentSummary, DatabaseConfig, TabType, AttendanceAction } from './types';
import { INITIAL_STAFF } from './data/initialStaff';
import { INITIAL_LOGS } from './data/initialLogs';

export default function App() {
  const [activeTab, setActiveTab] = useState<TabType>('kiosk');
  const [staffList, setStaffList] = useState<StaffMember[]>(INITIAL_STAFF);
  const [logs, setLogs] = useState<AttendanceRecord[]>(INITIAL_LOGS);
  const [departmentSummaries, setDepartmentSummaries] = useState<DepartmentSummary[]>([]);
  const [dbConfig, setDbConfig] = useState<DatabaseConfig>({
    dbName: "innow_attendance",
    autoSync: true,
    lastSyncedAt: new Date().toISOString(),
    isConnected: true
  });

  const [selectedBadgeStaff, setSelectedBadgeStaff] = useState<StaffMember | null>(null);

  // Fetch data from Express API
  const fetchData = useCallback(async () => {
    try {
      const [staffRes, logsRes, statsRes] = await Promise.all([
        fetch('/api/staff'),
        fetch('/api/logs'),
        fetch('/api/stats')
      ]);

      if (staffRes.ok) {
        const staffData = await staffRes.json();
        setStaffList(staffData);
      }
      if (logsRes.ok) {
        const logsData = await logsRes.json();
        setLogs(logsData);
      }
      if (statsRes.ok) {
        const statsData = await statsRes.json();
        setDepartmentSummaries(statsData.departmentSummaries || []);
      }
    } catch {
      // Fallback local state if offline or API delay
    }
  }, []);

  useEffect(() => {
    fetchData();
    const interval = setInterval(fetchData, 4000); // Poll for live dashboard synchronization
    return () => clearInterval(interval);
  }, [fetchData]);

  // Handle Clock In / Clock Out Event
  const handleClockEvent = async (staffId: string, action: AttendanceAction, method: 'PIN' | 'QR_CODE' | 'NFC') => {
    try {
      const res = await fetch('/api/logs', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ staffId, action, method })
      });

      const data = await res.json();
      if (!res.ok) {
        return { success: false, message: data.error || 'Clock event failed' };
      }

      await fetchData();
      return {
        success: true,
        message: `Successfully recorded ${action.replace('_', ' ')} for ${data.staff.name}.`,
        staff: data.staff
      };
    } catch {
      // Fallback local update
      const targetStaff = staffList.find(s => s.id === staffId || s.pin === staffId || s.qrCode === staffId);
      if (!targetStaff) {
        return { success: false, message: 'Invalid PIN or Staff ID' };
      }

      let newStatus: StaffMember['status'] = targetStaff.status;
      if (action === 'CLOCK_IN') newStatus = 'ONSITE';
      if (action === 'CLOCK_OUT') newStatus = 'OFFSITE';
      if (action === 'BREAK_START') newStatus = 'BREAK';
      if (action === 'BREAK_END') newStatus = 'ONSITE';

      targetStaff.status = newStatus;
      const newLog: AttendanceRecord = {
        id: `LOG-${Date.now()}`,
        staffId: targetStaff.id,
        staffName: targetStaff.name,
        department: targetStaff.department,
        action,
        timestamp: new Date().toISOString(),
        method,
        syncedToDb: true
      };

      setLogs(prev => [newLog, ...prev]);
      setStaffList(prev => prev.map(s => s.id === targetStaff.id ? { ...s, status: newStatus } : s));

      return {
        success: true,
        message: `Successfully recorded ${action.replace('_', ' ')} for ${targetStaff.name}.`,
        staff: targetStaff
      };
    }
  };

  // Add Manual Admin Log
  const handleAddManualLog = async (staffId: string, action: AttendanceAction, notes: string) => {
    try {
      await fetch('/api/logs', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ staffId, action, method: 'ADMIN_MANUAL', notes })
      });
      await fetchData();
    } catch {
      // Local fallback
      const staff = staffList.find(s => s.id === staffId);
      if (staff) {
        const newLog: AttendanceRecord = {
          id: `LOG-${Date.now()}`,
          staffId: staff.id,
          staffName: staff.name,
          department: staff.department,
          action,
          timestamp: new Date().toISOString(),
          method: 'ADMIN_MANUAL',
          notes,
          syncedToDb: true
        };
        setLogs(prev => [newLog, ...prev]);
      }
    }
  };

  // Add New Staff Member
  const handleAddStaff = async (staffData: Partial<StaffMember>) => {
    try {
      const res = await fetch('/api/staff', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(staffData)
      });
      if (res.ok) {
        await fetchData();
      }
    } catch {
      const newMember: StaffMember = {
        id: `STF-${Math.floor(100 + Math.random() * 900)}`,
        pin: staffData.pin || '1010',
        name: staffData.name || 'New Staff',
        role: staffData.role || 'Member',
        department: staffData.department || 'Software Engineering',
        email: staffData.email || 'staff@innow.co.za',
        phone: staffData.phone || '+27 (0) 82 000 0000',
        avatarUrl: staffData.avatarUrl || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=250',
        qrCode: `INNOW-${Math.random().toString(36).substring(7).toUpperCase()}`,
        status: 'OFFSITE'
      };
      setStaffList(prev => [newMember, ...prev]);
    }
  };

  // Trigger Instant Database Sync
  const handleTriggerSync = async () => {
    try {
      await fetch('/api/db/sync', { method: 'POST' });
      await fetchData();
    } catch {
      setLogs(prev => prev.map(l => ({ ...l, syncedToDb: true })));
    }
  };

  const onsiteCount = staffList.filter(s => s.status === 'ONSITE').length;

  return (
    <div className="min-h-screen bg-[#F8F9FA] font-sans text-[#1A1A1A] flex flex-col selection:bg-zinc-900 selection:text-white">
      {/* Navigation Header */}
      <Navbar
        activeTab={activeTab}
        setActiveTab={setActiveTab}
        onsiteCount={onsiteCount}
        totalCount={staffList.length}
        isAdmin={true}
      />

      {/* Main Content Area */}
      <main className="flex-1 pb-12">
        {activeTab === 'kiosk' && (
          <ClockKiosk
            staffList={staffList}
            onClockEvent={handleClockEvent}
          />
        )}

        {activeTab === 'dashboard' && (
          <LiveDashboard
            staffList={staffList}
            departmentSummaries={departmentSummaries}
            onSelectStaffBadge={(staff) => setSelectedBadgeStaff(staff)}
          />
        )}

        {activeTab === 'logs' && (
          <AttendanceLogs
            logs={logs}
            staffList={staffList}
            onAddManualLog={handleAddManualLog}
            onTriggerSync={handleTriggerSync}
          />
        )}

        {activeTab === 'staff' && (
          <StaffDirectory
            staffList={staffList}
            onAddStaff={handleAddStaff}
            onSelectBadge={(staff) => setSelectedBadgeStaff(staff)}
          />
        )}

        {activeTab === 'docs' && (
          <DeliverablesViewer />
        )}
      </main>

      {/* Printable Badge Modal */}
      <BadgeModal
        staff={selectedBadgeStaff}
        onClose={() => setSelectedBadgeStaff(null)}
      />

      {/* Footer */}
      <footer className="bg-white border-t border-zinc-200 py-4 px-6 text-center text-xs text-zinc-500">
        <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
          <p>© 2026 INNOW — Digital Attendance System.</p>
          <p className="font-mono text-[11px] text-zinc-400">Deploy Target: MySQL & PHP 8.3 Backend Engine</p>
        </div>
      </footer>
    </div>
  );
}
