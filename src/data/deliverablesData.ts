export interface DeliverableDoc {
  id: string;
  title: string;
  subtitle: string;
  category: 'User Guides' | 'Case Study' | 'Staff Training' | 'Architecture';
  updatedAt: string;
  sections: {
    heading: string;
    content: string;
    bullets?: string[];
  }[];
}

export const DELIVERABLES_DOCS: DeliverableDoc[] = [
  {
    id: 'user-guide-staff',
    title: 'Staff Member Quick-Start User Guide',
    subtitle: 'How to clock in, out, and register breaks using PIN, QR code, or NFC touchless tap.',
    category: 'User Guides',
    updatedAt: '2026-06-01',
    sections: [
      {
        heading: '1. Clocking In via Kiosk Station',
        content: 'When arriving onsite at the LC Studio facility:',
        bullets: [
          'Locate the dedicated tablet/kiosk at the entrance foyer.',
          'Option A (PIN): Key in your 4-digit PIN using the physical or on-screen keypad and touch "Clock In".',
          'Option B (QR Badge): Hold your digital or printed Staff Badge in front of the kiosk camera sensor.',
          'Option C (NFC Touchless): Tap your NFC card or smartphone badge on the designated reader.'
        ]
      },
      {
        heading: '2. Taking Breaks & Clocking Out',
        content: 'To maintain precise onsite occupancy for health and safety compliance:',
        bullets: [
          'Select "Start Break" or "End Break" before entering your PIN or scanning your badge.',
          'When leaving the building for the day, select "Clock Out" and scan/enter PIN.',
          'The status light on screen will flash GREEN for successful clock-in, BLUE for break, and GRAY for clock-out.'
        ]
      },
      {
        heading: '3. Duplicate Entry & Error Handling',
        content: 'The system prevents accidental double-tapping:',
        bullets: [
          'If you try to Clock In while already recorded as Onsite, the screen will display a friendly reminder.',
          'Incorrect PIN entries show an instant error toast and log invalid attempts for security.'
        ]
      }
    ]
  },
  {
    id: 'user-guide-admin',
    title: 'Administrator & Safety Officer Manual',
    subtitle: 'Managing live presence, emergency roll calls, manual log overrides, and Google Sheets exports.',
    category: 'User Guides',
    updatedAt: '2026-06-02',
    sections: [
      {
        heading: '1. Live Onsite Presence Dashboard',
        content: 'The Live Dashboard provides real-time visibility into current staff occupancy:',
        bullets: [
          'Filter onsite staff by department (Engineering, Design, IT Ops, Administration).',
          'View exact entry timestamps and active duration on site.',
          'Search staff by name or email in case of urgent client inquiries.'
        ]
      },
      {
        heading: '2. Emergency Evacuation Roll Call Mode',
        content: 'In the event of a fire drill or emergency building clearance:',
        bullets: [
          'Click the red "Emergency Roll Call" button at the top of the header or dashboard.',
          'The system renders a high-contrast emergency manifest listing ALL staff currently Onsite.',
          'Safety marshals can mark staff off as accounted for with interactive checkboxes and print/export the PDF manifest instantly.'
        ]
      },
      {
        heading: '3. Google Sheets Integration & CSV Audits',
        content: 'Exporting attendance logs for payroll and client reporting:',
        bullets: [
          'Go to the Google Sheets Integration tab to verify live synchronization status.',
          'Copy the pre-configured Google Apps Script code snippet directly into your company spreadsheet.',
          'Export formatted CSVs with a single click at any time.'
        ]
      }
    ]
  },
  {
    id: 'case-study-lc-studio',
    title: 'Project Case Study: LC Studio Cost-Effective Attendance Solution',
    subtitle: 'Achieving zero recurring hosting overhead while maintaining real-time reliability.',
    category: 'Case Study',
    updatedAt: '2026-06-03',
    sections: [
      {
        heading: 'Executive Summary',
        content: 'LC Studio required a modern, reliable digital attendance tracking system to replace error-prone manual sign-in sheets. A primary constraint was strict budget control, eliminating high monthly cloud hosting fees.'
      },
      {
        heading: 'Deployment & Hosting Decision Analysis',
        content: 'Evaluating single-location hosting options as specified in client feedback:',
        bullets: [
          'Option 1 (Chosen): Web hosting via LC Studio Xneelo shared web account - Provides 99.9% uptime with zero extra hosting charges by utilizing existing infrastructure.',
          'Option 2 (Alternative): Local offline kiosk hosting on local hardware - Zero external server dependency, operating entirely within local network subnet.'
        ]
      },
      {
        heading: 'Quantifiable Results & Benefits',
        content: 'Key outcomes delivered upon rollout:',
        bullets: [
          '100% reduction in paper log sheet waste and manual data re-entry time.',
          'Real-time emergency roll call accuracy updated within 1.5 seconds of check-in.',
          'Seamless sync to Google Sheets for automated HR & payroll processing.'
        ]
      }
    ]
  },
  {
    id: 'staff-training-plan',
    title: 'Staff Training & Change Management Program',
    subtitle: '4-week rollout schedule, training modules, and cheat sheets for staff adoption.',
    category: 'Staff Training',
    updatedAt: '2026-06-04',
    sections: [
      {
        heading: 'Training Timeline & Modules',
        content: 'Structured staff onboarding program spanning Sprint 1 & Sprint 2:',
        bullets: [
          'Module 1: 5-Minute Kiosk Orientation (Interactive walk-through at lobby entrance).',
          'Module 2: Digital ID Badge Issuance (Printing QR badges for all team members).',
          'Module 3: Department Coordinator Training (Emergency Roll Call procedures & manual logging).'
        ]
      },
      {
        heading: 'Frequently Asked Questions (FAQ)',
        content: 'Common staff queries during training:',
        bullets: [
          'Q: What if I forget my PIN? -> A: Your line manager or admin can reset your 4-digit PIN instantly in the Staff Directory.',
          'Q: Does the system track my location outside the building? -> A: No. The system strictly records entry and exit timestamps at the building door.'
        ]
      }
    ]
  }
];
