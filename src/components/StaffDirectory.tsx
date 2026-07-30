import React, { useState } from 'react';
import { 
  Building2, 
  UserPlus, 
  Search, 
  Phone, 
  Mail, 
  QrCode, 
  KeyRound, 
  ShieldCheck, 
  UserCheck,
  Plus
} from 'lucide-react';
import { StaffMember } from '../types';

interface StaffDirectoryProps {
  staffList: StaffMember[];
  onAddStaff: (staffData: Partial<StaffMember>) => Promise<void>;
  onSelectBadge: (staff: StaffMember) => void;
}

export const StaffDirectory: React.FC<StaffDirectoryProps> = ({
  staffList,
  onAddStaff,
  onSelectBadge
}) => {
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [deptFilter, setDeptFilter] = useState<string>('ALL');
  const [showAddModal, setShowAddModal] = useState<boolean>(false);

  // New staff state
  const [name, setName] = useState<string>('');
  const [role, setRole] = useState<string>('');
  const [department, setDepartment] = useState<string>('Software Engineering');
  const [email, setEmail] = useState<string>('');
  const [phone, setPhone] = useState<string>('');
  const [pin, setPin] = useState<string>('');
  const [emergencyContact, setEmergencyContact] = useState<string>('');
  const [submitting, setSubmitting] = useState<boolean>(false);

  const filteredStaff = staffList.filter(s => {
    const matchesSearch = 
      s.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
      s.id.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesDept = deptFilter === 'ALL' || s.department === deptFilter;
    return matchesSearch && matchesDept;
  });

  const handleCreateStaff = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name || !pin) return;

    setSubmitting(true);
    await onAddStaff({
      name,
      role: role || 'Team Member',
      department,
      email: email || `${name.toLowerCase().replace(/\s+/g, '.')}@lcstudio.co.za`,
      phone: phone || '+27 (0) 82 000 0000',
      pin,
      emergencyContact,
      avatarUrl: `https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=250`
    });

    setSubmitting(false);
    setShowAddModal(false);
    setName('');
    setRole('');
    setEmail('');
    setPhone('');
    setPin('');
    setEmergencyContact('');
  };

  return (
    <div className="max-w-7xl mx-auto p-4 sm:p-6 space-y-6">
      {/* Header */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl font-extrabold tracking-tight flex items-center gap-2 text-zinc-900">
            <Building2 className="w-5 h-5 text-emerald-600" />
            <span>LC Studio Staff Directory & Badge Control</span>
          </h2>
          <p className="text-xs text-zinc-500 mt-0.5">
            Manage staff authorization PINs, department assignments, and digital access passes.
          </p>
        </div>

        <button
          onClick={() => setShowAddModal(true)}
          className="px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white font-bold text-xs rounded-xl transition-all shadow-xs flex items-center gap-2 cursor-pointer active:scale-95 shrink-0"
        >
          <UserPlus className="w-4 h-4 text-emerald-400" />
          <span>ADD NEW STAFF MEMBER</span>
        </button>
      </div>

      {/* Filter Bar */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-4 text-zinc-900 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div className="relative w-full sm:w-72">
          <Search className="w-4 h-4 text-zinc-400 absolute left-3 top-2.5" />
          <input
            type="text"
            placeholder="Search staff, email, ID..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full bg-zinc-50 border border-zinc-200 rounded-xl pl-9 pr-3 py-2 text-xs text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-zinc-400"
          />
        </div>

        <select
          value={deptFilter}
          onChange={(e) => setDeptFilter(e.target.value)}
          className="w-full sm:w-auto bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs text-zinc-800 focus:outline-none focus:border-zinc-400"
        >
          <option value="ALL">All Departments</option>
          <option value="Software Engineering">Software Engineering</option>
          <option value="Design & UX">Design & UX</option>
          <option value="IT Operations">IT Operations</option>
          <option value="Project Management">Project Management</option>
          <option value="Administration">Administration</option>
        </select>
      </div>

      {/* Staff Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {filteredStaff.map((s) => (
          <div
            key={s.id}
            className="bg-white border border-zinc-200 hover:border-zinc-300 rounded-2xl p-5 text-zinc-900 shadow-xs space-y-4 transition-all group relative overflow-hidden"
          >
            <div className="flex items-start justify-between">
              <div className="flex items-center gap-3">
                <img
                  src={s.avatarUrl}
                  alt={s.name}
                  className="w-12 h-12 rounded-2xl object-cover ring-2 ring-zinc-200 group-hover:ring-zinc-400 transition-all"
                />
                <div>
                  <h3 className="font-bold text-base text-zinc-900 group-hover:text-zinc-700 transition-colors">{s.name}</h3>
                  <p className="text-xs text-zinc-800 font-semibold">{s.role}</p>
                  <span className="text-[10px] text-zinc-500">{s.department}</span>
                </div>
              </div>

              <span className={`px-2 py-0.5 rounded text-[10px] font-extrabold font-mono ${
                s.status === 'ONSITE' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' :
                s.status === 'BREAK' ? 'bg-amber-50 text-amber-800 border border-amber-200' :
                'bg-zinc-100 text-zinc-600 border border-zinc-200'
              }`}>
                {s.status}
              </span>
            </div>

            <div className="bg-zinc-50 p-3 rounded-xl border border-zinc-200 space-y-1.5 text-xs text-zinc-700 font-mono">
              <div className="flex justify-between">
                <span className="text-zinc-500">ID & PIN:</span>
                <span className="font-bold text-zinc-900">{s.id} • PIN: <span className="text-emerald-700">{s.pin}</span></span>
              </div>
              <div className="flex items-center gap-1.5 text-zinc-600 truncate">
                <Mail className="w-3.5 h-3.5 text-zinc-400 shrink-0" />
                <span className="truncate">{s.email}</span>
              </div>
              <div className="flex items-center gap-1.5 text-zinc-600">
                <Phone className="w-3.5 h-3.5 text-zinc-400 shrink-0" />
                <span>{s.phone}</span>
              </div>
            </div>

            <button
              onClick={() => onSelectBadge(s)}
              className="w-full py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-xl transition-colors cursor-pointer border border-zinc-200 flex items-center justify-center gap-1.5"
            >
              <QrCode className="w-4 h-4 text-zinc-700" />
              <span>PRINT DIGITAL BADGE</span>
            </button>
          </div>
        ))}
      </div>

      {/* Add Staff Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white border border-zinc-200 rounded-2xl max-w-lg w-full p-6 text-zinc-900 shadow-xl space-y-4">
            <h3 className="text-lg font-bold flex items-center gap-2">
              <UserPlus className="w-5 h-5 text-zinc-900" />
              <span>Register New Staff Member</span>
            </h3>

            <form onSubmit={handleCreateStaff} className="space-y-3 text-xs">
              <div>
                <label className="block text-zinc-700 font-semibold mb-1">Full Name *</label>
                <input
                  type="text"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="e.g. Kagiso Mokoena"
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-zinc-400"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-zinc-700 font-semibold mb-1">Role Title</label>
                  <input
                    type="text"
                    value={role}
                    onChange={(e) => setRole(e.target.value)}
                    placeholder="e.g. Software Developer"
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-zinc-400"
                  />
                </div>

                <div>
                  <label className="block text-zinc-700 font-semibold mb-1">Department</label>
                  <select
                    value={department}
                    onChange={(e) => setDepartment(e.target.value)}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-zinc-400"
                  >
                    <option value="Software Engineering">Software Engineering</option>
                    <option value="Design & UX">Design & UX</option>
                    <option value="IT Operations">IT Operations</option>
                    <option value="Project Management">Project Management</option>
                    <option value="Administration">Administration</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-zinc-700 font-semibold mb-1">Email</label>
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="name@lcstudio.co.za"
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-zinc-400"
                  />
                </div>

                <div>
                  <label className="block text-zinc-700 font-semibold mb-1">Authorization PIN (4 digits) *</label>
                  <input
                    type="text"
                    required
                    maxLength={6}
                    value={pin}
                    onChange={(e) => setPin(e.target.value)}
                    placeholder="1009"
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 font-mono focus:outline-none focus:border-zinc-400"
                  />
                </div>
              </div>

              <div>
                <label className="block text-zinc-700 font-semibold mb-1">Phone Number</label>
                <input
                  type="text"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  placeholder="+27 (0) 82 123 4567"
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-zinc-400"
                />
              </div>

              <div>
                <label className="block text-zinc-700 font-semibold mb-1">In Case of Emergency (ICE) Contact</label>
                <input
                  type="text"
                  value={emergencyContact}
                  onChange={(e) => setEmergencyContact(e.target.value)}
                  placeholder="Spouse Name & Phone Number"
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-zinc-400"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl text-zinc-700 font-semibold cursor-pointer transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={submitting}
                  className="px-4 py-2 bg-zinc-900 hover:bg-zinc-800 rounded-xl text-white font-bold cursor-pointer transition-colors shadow-xs"
                >
                  {submitting ? 'Registering...' : 'Add Staff Member'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
