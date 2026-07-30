import React, { useState, useEffect } from 'react';
import { 
  Building2, 
  Users, 
  Clock, 
  UserCheck, 
  BookOpen, 
  ShieldCheck,
  Radio
} from 'lucide-react';
import { TabType } from '../types';

interface NavbarProps {
  activeTab: TabType;
  setActiveTab: (tab: TabType) => void;
  onsiteCount: number;
  totalCount: number;
  isAdmin?: boolean;
}

export const Navbar: React.FC<NavbarProps> = ({
  activeTab,
  setActiveTab,
  onsiteCount,
  totalCount,
  isAdmin = false
}) => {
  const [time, setTime] = useState<string>('');
  const [date, setDate] = useState<string>('');

  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      setTime(now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
      setDate(now.toLocaleDateString([], { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }));
    };
    updateTime();
    const interval = setInterval(updateTime, 1000);
    return () => clearInterval(interval);
  }, []);

  const tabs = [
    { id: 'kiosk' as TabType, label: 'Clock Kiosk', icon: Clock },
    { id: 'dashboard' as TabType, label: 'Live Dashboard', icon: Users, badge: onsiteCount },
    ...(isAdmin ? [{ id: 'logs' as TabType, label: 'Audit Logs', icon: UserCheck }] : []),
    { id: 'staff' as TabType, label: 'Staff Directory', icon: Building2 },
    { id: 'docs' as TabType, label: 'Deliverables & Guides', icon: BookOpen },
  ];

  return (
    <header className="bg-white border-b border-zinc-200 text-zinc-900 sticky top-0 z-40 shadow-xs">
      {/* Top Banner */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        {/* Brand */}
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 rounded-xl bg-red-600 flex items-center justify-center shadow-xs text-white">
            <div className="relative flex items-center justify-center">
              <ShieldCheck className="w-6 h-6 text-white" />
              <div className="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-400 rounded-full animate-ping" />
            </div>
          </div>
          <div>
            <div className="flex items-center gap-2">
              <span className="font-bold text-lg tracking-tight text-zinc-900">INNOW</span>
              <span className="text-xs bg-red-50 text-red-700 font-bold px-2 py-0.5 rounded-md border border-red-200">
                v2.4
              </span>
            </div>
            <p className="text-xs text-zinc-500">Digital Attendance System</p>
          </div>
        </div>

        {/* Live Headcount */}
        <div className="flex items-center gap-3 flex-wrap">
          <div className="bg-zinc-50 border border-zinc-200 rounded-xl px-3.5 py-1.5 flex items-center gap-3">
            <div className="flex items-center gap-2">
              <Radio className="w-4 h-4 text-emerald-600 animate-pulse" />
              <span className="text-xs text-zinc-500 font-medium">Onsite Staff:</span>
            </div>
            <div className="flex items-baseline gap-1">
              <span className="text-lg font-extrabold text-emerald-700">{onsiteCount}</span>
              <span className="text-xs text-zinc-400">/ {totalCount}</span>
            </div>
          </div>

          <div className="hidden sm:flex flex-col text-right px-2">
            <span className="text-sm font-mono font-bold text-zinc-800">{time}</span>
            <span className="text-[11px] text-zinc-500">{date}</span>
          </div>
        </div>
      </div>

      {/* Navigation Tabs */}
      <div className="bg-zinc-50/80 border-t border-zinc-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <nav className="flex space-x-1 sm:space-x-2 overflow-x-auto py-2 scrollbar-none">
            {tabs.map((tab) => {
              const Icon = tab.icon;
              const isActive = activeTab === tab.id;
              return (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium whitespace-nowrap transition-all cursor-pointer ${
                    isActive
                      ? 'bg-zinc-900 text-white font-semibold shadow-xs'
                      : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-200/60'
                  }`}
                >
                  <Icon className={`w-4 h-4 ${isActive ? 'text-white' : 'text-zinc-500'}`} />
                  <span>{tab.label}</span>
                  {tab.badge !== undefined && (
                    <span
                      className={`ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold ${
                        isActive
                          ? 'bg-white/20 text-white'
                          : 'bg-emerald-100 text-emerald-800 border border-emerald-200'
                      }`}
                    >
                      {tab.badge}
                    </span>
                  )}
                </button>
              );
            })}
          </nav>
        </div>
      </div>
    </header>
  );
};
