import { AttendanceRecord } from '../types';

export const INITIAL_LOGS: AttendanceRecord[] = [
  {
    id: 'LOG-1001',
    staffId: 'STF-101',
    staffName: 'Sandi Liviwe',
    department: 'Software Engineering',
    action: 'CLOCK_IN',
    timestamp: new Date(Date.now() - 3.5 * 3600 * 1000).toISOString(),
    method: 'PIN',
    syncedToDb: true,
    notes: 'Morning clock in'
  },
  {
    id: 'LOG-1002',
    staffId: 'STF-102',
    staffName: 'Ketan Patel',
    department: 'Software Engineering',
    action: 'CLOCK_IN',
    timestamp: new Date(Date.now() - 4 * 3600 * 1000).toISOString(),
    method: 'QR_CODE',
    syncedToDb: true,
    notes: 'Scanned digital badge'
  },
  {
    id: 'LOG-1003',
    staffId: 'STF-103',
    staffName: 'Amina Ndlovu',
    department: 'Design & UX',
    action: 'CLOCK_IN',
    timestamp: new Date(Date.now() - 5 * 3600 * 1000).toISOString(),
    method: 'PIN',
    syncedToDb: true
  },
  {
    id: 'LOG-1004',
    staffId: 'STF-103',
    staffName: 'Amina Ndlovu',
    department: 'Design & UX',
    action: 'BREAK_START',
    timestamp: new Date(Date.now() - 0.5 * 3600 * 1000).toISOString(),
    method: 'NFC',
    syncedToDb: true,
    notes: 'Lunch break'
  },
  {
    id: 'LOG-1005',
    staffId: 'STF-104',
    staffName: 'David van der Merwe',
    department: 'IT Operations',
    action: 'CLOCK_IN',
    timestamp: new Date(Date.now() - 2 * 3600 * 1000).toISOString(),
    method: 'NFC',
    syncedToDb: true
  },
  {
    id: 'LOG-1006',
    staffId: 'STF-106',
    staffName: 'Tariq Jacobs',
    department: 'Software Engineering',
    action: 'CLOCK_IN',
    timestamp: new Date(Date.now() - 3 * 3600 * 1000).toISOString(),
    method: 'QR_CODE',
    syncedToDb: true
  },
  {
    id: 'LOG-1007',
    staffId: 'STF-108',
    staffName: 'Gareth Smith',
    department: 'Administration',
    action: 'CLOCK_IN',
    timestamp: new Date(Date.now() - 6 * 3600 * 1000).toISOString(),
    method: 'PIN',
    syncedToDb: true
  }
];
