'use client';

import { useState, useEffect } from 'react';
import { Toggle } from '@/components/ui/Toggle';
import { createClient } from '@/lib/supabase/client';
import type { ModuleFlag } from '@/types/database';

const ROLE_TAG: Record<string, { label: string; color: string }> = {
  inc_filing:     { label: 'Student',    color: 'bg-green-100 text-green-700' },
  grade_input:    { label: 'Instructor', color: 'bg-blue-100 text-blue-700' },
  dept_approval:  { label: 'Dept. Head', color: 'bg-purple-100 text-purple-700' },
  receipt_upload: { label: 'Student',    color: 'bg-green-100 text-green-700' },
  or_verification:{ label: 'Registrar',  color: 'bg-rose-100 text-rose-700' },
  grade_posting:  { label: 'Registrar',  color: 'bg-rose-100 text-rose-700' },
  pdf_generation: { label: 'Auto',       color: 'bg-gray-100 text-gray-600' },
  email_notify:   { label: 'SMTP',       color: 'bg-gray-100 text-gray-600' },
};

const MODULE_DESCRIPTIONS: Record<string, string> = {
  inc_filing:      'Allows students to initiate and submit INC completion applications',
  grade_input:     'Instructor enters resolved grade and applies e-signature',
  dept_approval:   'Department Head reviews, approves, and e-signs instructor submissions',
  receipt_upload:  'Student uploads Official Receipt image after payment',
  or_verification: 'Registrar split-view OR ledger comparison and verification',
  grade_posting:   'Registrar finalizes and officially posts grades to transcripts',
  pdf_generation:  'Auto-generates A4 INC completion document upon resolution',
  email_notify:    'Sends transactional emails at every workflow state change',
};

export default function AdminModulesPage() {
  const [modules, setModules] = useState<ModuleFlag[]>([]);
  const [saving, setSaving] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const supabase = createClient();

  useEffect(() => {
    supabase.from('module_flags').select('*').order('key')
      .then(({ data }) => { setModules(data ?? []); setLoading(false); });
  }, []);

  async function toggle(key: string, value: boolean) {
    setSaving(key);
    const updated = modules.map(m => m.key === key ? { ...m, enabled: value } : m);
    setModules(updated);

    await supabase
      .from('module_flags')
      .update({ enabled: value, updated_at: new Date().toISOString() })
      .eq('key', key);

    // Log to audit_logs via API route
    await fetch('/api/modules', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ key, enabled: value }),
    });

    setSaving(null);
  }

  const enabledCount = modules.filter(m => m.enabled).length;

  return (
    <div className="p-8">
      <div className="flex items-start justify-between mb-7">
        <div>
          <h1 className="font-serif text-2xl font-semibold text-gray-900">Module Control</h1>
          <p className="text-sm text-gray-400 mt-0.5">
            {loading ? 'Loading…' : `${enabledCount} of ${modules.length} modules enabled`}
          </p>
        </div>
        <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700">
          Admin Only
        </span>
      </div>

      <div className="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3.5 mb-6 text-sm text-amber-800">
        <strong>Note:</strong> Disabling a module hides its UI and blocks its API routes for all users.
        Changes take effect immediately — no restart required.
      </div>

      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        {loading ? (
          <div className="py-16 text-center text-sm text-gray-400">Loading modules…</div>
        ) : (
          modules.map((mod, i) => {
            const tag = ROLE_TAG[mod.key];
            const desc = MODULE_DESCRIPTIONS[mod.key] ?? '';
            return (
              <div
                key={mod.key}
                className={`flex items-center justify-between px-6 py-4 ${
                  i < modules.length - 1 ? 'border-b border-gray-100' : ''
                }`}
              >
                <div className="flex-1 min-w-0 pr-8">
                  <div className="flex items-center gap-2 mb-0.5">
                    <h3 className="text-[13.5px] font-medium text-gray-900">{mod.label}</h3>
                    {tag && (
                      <span className={`text-[10.5px] font-medium px-2 py-0.5 rounded-full ${tag.color}`}>
                        {tag.label}
                      </span>
                    )}
                    {saving === mod.key && (
                      <span className="text-[10.5px] text-gray-400 animate-pulse">Saving…</span>
                    )}
                  </div>
                  <p className="text-[12px] text-gray-400">{desc}</p>
                </div>
                <Toggle
                  checked={mod.enabled}
                  onChange={val => toggle(mod.key, val)}
                  disabled={saving !== null}
                />
              </div>
            );
          })
        )}
      </div>
    </div>
  );
}
