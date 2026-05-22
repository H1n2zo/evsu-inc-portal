import { cn } from '@/lib/utils';
import { type ButtonHTMLAttributes } from 'react';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  size?: 'sm' | 'md';
}

const VARIANTS = {
  primary:   'bg-[#6B0F1A] text-white hover:bg-[#4A0A12] focus-visible:ring-[#6B0F1A]',
  secondary: 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50',
  danger:    'bg-white text-red-700 border border-red-200 hover:bg-red-50',
  ghost:     'text-gray-500 hover:text-gray-900 hover:bg-gray-100',
};

const SIZES = {
  sm: 'px-3 py-1.5 text-xs rounded-md',
  md: 'px-4 py-2 text-sm rounded-lg',
};

export function Button({ variant = 'secondary', size = 'sm', className, children, ...props }: ButtonProps) {
  return (
    <button
      className={cn(
        'inline-flex items-center gap-1.5 font-medium transition-colors',
        'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        VARIANTS[variant],
        SIZES[size],
        className
      )}
      {...props}
    >
      {children}
    </button>
  );
}
