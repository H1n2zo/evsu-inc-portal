import { type ClassValue, clsx } from 'clsx';

export function cn(...inputs: ClassValue[]) {
  return clsx(inputs);
}

export function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric',
  });
}

export function formatDateTime(iso: string): string {
  return new Date(iso).toLocaleString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit', second: '2-digit',
    hour12: false,
  });
}

export function formatPeso(amount: number): string {
  return `₱${amount.toLocaleString('en-PH')}`;
}

export function getInitials(name: string): string {
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}

export const STATUS_LABELS: Record<string, string> = {
  draft:    'Draft',
  step_1:   'Filed',
  step_2:   'Instructor Review',
  step_3:   'Dept. Head Review',
  step_4:   'Awaiting Payment',
  step_5:   'OR Verification',
  step_6:   'Grade Posting',
  resolved: 'Resolved',
  rejected: 'Rejected',
};

export const STATUS_BADGE: Record<string, string> = {
  draft:    'bg-gray-100 text-gray-600',
  step_1:   'bg-blue-50 text-blue-700',
  step_2:   'bg-blue-50 text-blue-700',
  step_3:   'bg-purple-50 text-purple-700',
  step_4:   'bg-amber-50 text-amber-700',
  step_5:   'bg-amber-50 text-amber-700',
  step_6:   'bg-amber-50 text-amber-700',
  resolved: 'bg-green-50 text-green-700',
  rejected: 'bg-red-50 text-red-700',
};
