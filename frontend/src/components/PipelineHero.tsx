import { GENERATOR_META } from '../lib/meta'
import type { GeneratorKey } from '../types/report'

const OUTPUTS: GeneratorKey[] = ['dompdf', 'snappy', 'puppeteer', 'excel', 'csv', 'docx']
const ROW = 44
const TOP = 28

/** Animated diagram of the product: data goes into the hub and leaves as six formats. */
export default function PipelineHero() {
  return (
    <svg
      viewBox="0 0 560 300"
      className="h-auto w-full"
      style={{ fontFamily: '"Space Grotesk", system-ui, sans-serif' }}
      role="img"
      aria-label="Data flows through the report hub into PDF, Excel, CSV and Word outputs"
    >
      <defs>
        <linearGradient id="hub" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stopColor="#22D3EE" />
          <stop offset="1" stopColor="#6366F1" />
        </linearGradient>
      </defs>

      {/* input */}
      <path d="M64 150H196" stroke="rgb(var(--line))" strokeWidth="2" fill="none" />
      <path d="M64 150H196" stroke="rgb(var(--accent))" strokeWidth="2" fill="none" strokeDasharray="6 6" className="animate-dash" opacity=".8" />
      <rect x="6" y="120" width="58" height="60" rx="10" fill="rgb(var(--panel))" stroke="rgb(var(--line))" />
      {[0, 1, 2].map((i) => (
        <rect key={i} x="15" y={132 + i * 13} width={i === 2 ? 20 : 40} height="6" rx="3" fill="rgb(var(--mute))" opacity=".7" />
      ))}
      <text x="35" y="202" textAnchor="middle" fontSize="14" fill="rgb(var(--mute))">Your data</text>

      {/* hub */}
      <circle cx="236" cy="150" r="40" fill="none" stroke="rgb(var(--accent))" strokeWidth="1.5" className="animate-ring" style={{ transformOrigin: '236px 150px' }} />
      <path d="M236 108 272 129v42l-36 21-36-21v-42z" fill="rgb(var(--panel))" stroke="url(#hub)" strokeWidth="3" strokeLinejoin="round" />
      <path d="M225 132h14l8 8v26a3 3 0 0 1-3 3h-19a3 3 0 0 1-3-3v-31a3 3 0 0 1 3-3z" fill="url(#hub)" />
      <path d="M227 152h16M227 159h16M227 166h9" stroke="#06101c" strokeWidth="2.2" strokeLinecap="round" />
      <text x="236" y="214" textAnchor="middle" fontSize="14" fill="rgb(var(--mute))">Report hub</text>

      {/* outputs */}
      {OUTPUTS.map((key, i) => {
        const meta = GENERATOR_META[key]
        const y = TOP + i * ROW + 10
        const d = `M276 150C340 150 350 ${y} 404 ${y}`
        const delay = `${i * 0.45}s`
        return (
          <g key={key}>
            <path d={d} stroke="rgb(var(--line))" strokeWidth="1.5" fill="none" />
            <circle r="3.5" fill={meta.color}>
              <animateMotion dur="3s" begin={delay} repeatCount="indefinite" path={d} />
              <animate attributeName="opacity" values="0;1;1;0" dur="3s" begin={delay} repeatCount="indefinite" />
            </circle>
            <circle cx="418" cy={y} r="14" fill="rgb(var(--panel))" stroke={meta.color} strokeWidth="1.8" />
            <circle cx="418" cy={y} r="5" fill={meta.color}>
              <animate attributeName="opacity" values="1;.3;1" dur="3s" begin={delay} repeatCount="indefinite" />
            </circle>
            <text x="440" y={y + 6} fontSize="18" fontWeight="500" fill="rgb(var(--ink))">{meta.label}</text>
          </g>
        )
      })}
    </svg>
  )
}
