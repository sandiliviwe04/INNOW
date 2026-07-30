import React, { useState } from 'react';
import { BookOpen, FileText, CheckCircle2, Award, Download, ShieldCheck, ChevronRight } from 'lucide-react';
import { DELIVERABLES_DOCS, DeliverableDoc } from '../data/deliverablesData';

export const DeliverablesViewer: React.FC = () => {
  const [selectedDocId, setSelectedDocId] = useState<string>(DELIVERABLES_DOCS[0].id);
  const selectedDoc = DELIVERABLES_DOCS.find(d => d.id === selectedDocId) || DELIVERABLES_DOCS[0];

  const handleDownloadDoc = (doc: DeliverableDoc) => {
    const textContent = `${doc.title}\n${doc.subtitle}\nUpdated: ${doc.updatedAt}\n\n` +
      doc.sections.map(s => `=== ${s.heading} ===\n${s.content}\n` + (s.bullets ? s.bullets.map(b => ` • ${b}`).join('\n') : '')).join('\n\n');

    const element = document.createElement("a");
    const file = new Blob([textContent], {type: 'text/plain'});
    element.href = URL.createObjectURL(file);
    element.download = `${doc.id}_LC_Studio_Deliverable.txt`;
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
  };

  return (
    <div className="max-w-7xl mx-auto p-4 sm:p-6 space-y-6">
      {/* Header */}
      <div className="bg-white border border-zinc-200 rounded-2xl p-5 text-zinc-900 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center gap-2">
            <span className="px-2 py-0.5 bg-zinc-100 text-zinc-800 border border-zinc-200 rounded text-[10px] font-bold">
              DELIVERABLES 1 - 5
            </span>
            <h2 className="text-xl font-extrabold tracking-tight text-zinc-900">Project Documentation & User Guides</h2>
          </div>
          <p className="text-xs text-zinc-500 mt-0.5">
            Complete project deliverables fulfilling LC Studio specification requirements.
          </p>
        </div>

        <button
          onClick={() => handleDownloadDoc(selectedDoc)}
          className="px-4 py-2 bg-zinc-900 hover:bg-zinc-800 rounded-xl text-xs font-bold text-white flex items-center gap-2 cursor-pointer transition-colors shrink-0 shadow-xs"
        >
          <Download className="w-4 h-4 text-emerald-400" />
          <span>Export Document</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        {/* Document Navigation Sidebar */}
        <div className="space-y-2">
          <p className="text-[11px] font-mono font-bold text-zinc-500 uppercase tracking-wider px-2">
            Select Deliverable:
          </p>
          {DELIVERABLES_DOCS.map((doc) => {
            const isSelected = doc.id === selectedDoc.id;
            return (
              <button
                key={doc.id}
                onClick={() => setSelectedDocId(doc.id)}
                className={`w-full text-left p-3.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between group ${
                  isSelected
                    ? 'bg-zinc-900 text-white border-zinc-900 shadow-xs font-bold'
                    : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
                }`}
              >
                <div className="space-y-0.5 pr-2">
                  <span className={`text-[10px] font-mono uppercase block ${isSelected ? 'text-zinc-300' : 'text-zinc-500'}`}>
                    {doc.category}
                  </span>
                  <p className="text-xs font-semibold leading-snug line-clamp-2">{doc.title}</p>
                </div>
                <ChevronRight className={`w-4 h-4 shrink-0 transition-transform ${isSelected ? 'text-white translate-x-1' : 'text-zinc-400 group-hover:text-zinc-700'}`} />
              </button>
            );
          })}
        </div>

        {/* Selected Document Content Area */}
        <div className="md:col-span-3 bg-white border border-zinc-200 rounded-2xl p-6 text-zinc-900 shadow-xs space-y-6">
          <div className="border-b border-zinc-200 pb-4 space-y-1">
            <span className="text-xs font-mono font-bold text-zinc-600 uppercase tracking-wider">
              LC STUDIO DELIVERABLE • {selectedDoc.category}
            </span>
            <h1 className="text-2xl font-extrabold text-zinc-900 tracking-tight">{selectedDoc.title}</h1>
            <p className="text-xs text-zinc-600">{selectedDoc.subtitle}</p>
            <p className="text-[10px] text-zinc-400 font-mono mt-1">Version Approved: {selectedDoc.updatedAt}</p>
          </div>

          <div className="space-y-6 text-zinc-800">
            {selectedDoc.sections.map((sec, idx) => (
              <div key={idx} className="bg-zinc-50 p-5 rounded-xl border border-zinc-200 space-y-3">
                <h3 className="text-base font-bold text-zinc-900 flex items-center gap-2">
                  <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>{sec.heading}</span>
                </h3>
                <p className="text-xs leading-relaxed text-zinc-700">{sec.content}</p>

                {sec.bullets && sec.bullets.length > 0 && (
                  <ul className="space-y-2 pt-1 pl-2">
                    {sec.bullets.map((bullet, bIdx) => (
                      <li key={bIdx} className="text-xs text-zinc-700 flex items-start gap-2">
                        <span className="w-1.5 h-1.5 rounded-full bg-zinc-900 mt-1.5 shrink-0" />
                        <span>{bullet}</span>
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
