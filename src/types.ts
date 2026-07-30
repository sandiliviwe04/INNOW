export type StaffStatus = 'ONSITE' | 'BREAK' | 'OFFSITE';

export type AttendanceAction = 'CLOCK_IN' | 'CLOCK_OUT' | 'BREAK_START' | 'BREAK_END';

export interface StaffMember {
  id: string;
  pin: string;
  name: string;
  role: string;
  department: string;
  email: string;
  phone: string;
  avatarUrl: string;
  qrCode: string;
  status: StaffStatus;
  lastClockIn?: string; // ISO string
  lastClockOut?: string; // ISO string
  emergencyContact?: string;
}

export interface AttendanceRecord {
  id: string;
  staffId: string;
  staffName: string;
  department: string;
  action: AttendanceAction;
  timestamp: string; // ISO string
  method: 'PIN' | 'QR_CODE' | 'NFC' | 'ADMIN_MANUAL';
  notes?: string;
  syncedToDb: boolean;
}

export interface DepartmentSummary {
  name: string;
  total: number;
  onsite: number;
  break: number;
  offsite: number;
}

export interface DatabaseConfig {
  dbName: string;
  autoSync: boolean;
  lastSyncedAt?: string;
  isConnected: boolean;
}

export interface AuditLog {
  id: string;
  timestamp: string;
  user: string;
  action: string;
  details: string;
}

export type TabType = 'checkin' | 'dashboard' | 'logs' | 'staff' | 'docs';
