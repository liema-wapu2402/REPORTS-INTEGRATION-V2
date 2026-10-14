import { useId } from 'react'

interface LogoMarkProps {
  size?: number
  animated?: boolean
  className?: string
}

/**
 * The Report Integrations mark: a hexagonal node (the integration hub)
 * holding a document, with two circuit traces feeding data in and out.
 * When animated, small packets travel along the traces.
 */
export function LogoMark({ size = 40, animated = true, className = '' }: LogoMarkProps) {
  const id = useId().replace(/:/g, '')
  const grad = `lg-${id}`

  return (
    <svg
      viewBox="0 0 64 64"
      width={size}
      height={size}
      className={className}
      role="img"
      aria-label="Report Integrations logo"
    >
      <defs>
        <linearGradient id={grad} x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stopColor="#22D3EE" />
          <stop offset="1" stopColor="#6366F1" />
        </linearGradient>
      </defs>

      <path
        d="M32 3 57 17.5v29L32 61 7 46.5v-29z"
        fill="rgb(var(--panel))"
        stroke={`url(#${grad})`}
        strokeWidth="2.5"
        strokeLinejoin="round"
      />

      {/* circuit traces */}
      <path d="M7 30h8v4h5" fill="none" stroke={`url(#${grad})`} strokeWidth="1.6" strokeLinecap="round" opacity=".7" />
      <path d="M57 34h-8v-4h-5" fill="none" stroke={`url(#${grad})`} strokeWidth="1.6" strokeLinecap="round" opacity=".7" />
      <circle cx="15" cy="30" r="1.8" fill="#22D3EE" />
      <circle cx="49" cy="34" r="1.8" fill="#818CF8" />

      {/* document */}
      <path
        d="M22 17h13l8 8v21a2 2 0 0 1-2 2H22a2 2 0 0 1-2-2V19a2 2 0 0 1 2-2z"
        fill={`url(#${grad})`}
      />
      <path d="M35 17v8h8z" fill="#ffffff" opacity=".4" />
      <path d="M25 32h13M25 37h13M25 42h7" stroke="#06101c" strokeWidth="2" strokeLinecap="round" />

      {animated && (
        <>
          <circle r="2" fill="#22D3EE">
            <animateMotion dur="2.4s" repeatCount="indefinite" path="M7 30h8v4h5" />
            <animate attributeName="opacity" values="0;1;1;0" dur="2.4s" repeatCount="indefinite" />
          </circle>
          <circle r="2" fill="#818CF8">
            <animateMotion dur="2.4s" begin="1.2s" repeatCount="indefinite" path="M44 30h5v4h8" />
            <animate attributeName="opacity" values="0;1;1;0" dur="2.4s" begin="1.2s" repeatCount="indefinite" />
          </circle>
        </>
      )}
    </svg>
  )
}

interface LogoProps {
  size?: number
  showTagline?: boolean
}

export default function Logo({ size = 40, showTagline = false }: LogoProps) {
  return (
    <div className="flex items-center gap-3">
      <div className="relative shrink-0">
        <div className="absolute inset-0 rounded-full bg-accent/30 blur-xl" aria-hidden />
        <LogoMark size={size} className="relative" />
      </div>
      <div className="leading-none">
        <div className="font-display text-[1.05rem] font-semibold tracking-tight">
          Report <span className="text-gradient">Integrations</span>
        </div>
        {showTagline && <div className="mt-1 text-xs text-mute">One dashboard. Every report format.</div>}
      </div>
    </div>
  )
}
