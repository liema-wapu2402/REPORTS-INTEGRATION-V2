import { useEffect, useMemo, useState } from 'react'
import { Check, FileText } from 'lucide-react'
import { api } from '../../lib/api'
import { GENERATOR_META } from '../../lib/meta'
import type { GeneratorInfo, GeneratorKey, SystemHealth } from '../../types/report'
import type { Orientation, PaperSize } from '../../hooks/useReportsTemplate'
import PageHeader from '../../components/PageHeader'
import SampleCard from '../../components/SampleCard'

const ENGINES: { key: Extract<GeneratorKey, 'dompdf' | 'snappy' | 'puppeteer'>; label: string; blurb: string }[] = [
  { key: 'dompdf', label: 'DomPDF', blurb: 'Pure PHP. No binaries, quickest to set up.' },
  { key: 'snappy', label: 'Snappy', blurb: 'wkhtmltopdf WebKit. Table based layouts.' },
  { key: 'puppeteer', label: 'Puppeteer', blurb: 'Headless Chrome. Best fidelity, needs Node.' },
]
const PAPER: PaperSize[] = ['A4', 'Letter', 'Legal']

export default function PdfReportPage() {
  const [driver, setDriver] = useState<(typeof ENGINES)[number]['key']>('dompdf')
  const [paper, setPaper] = useState<PaperSize | ''>('')
  const [orientation, setOrientation] = useState<Orientation | ''>('')
  const [catalog, setCatalog] = useState<GeneratorInfo[]>([])
  const [health, setHealth] = useState<SystemHealth | null>(null)

  useEffect(() => {
    api.get<{ generators: GeneratorInfo[] }>('/samples').then((r) => setCatalog(r.data.generators)).catch(() => undefined)
    api.get<SystemHealth>('/system/health').then((r) => setHealth(r.data)).catch(() => undefined)
  }, [])

  const samples = useMemo(() => catalog.find((g) => g.key === driver)?.samples ?? [], [catalog, driver])
  const engine = health?.engines.find((e) => e.key === driver)
  const disabledReason = engine && !engine.ready ? `Engine not ready. ${engine.fix ?? ''}` : null
  const overrides = { ...(paper ? { paper } : {}), ...(orientation ? { orientation } : {}) }

  return (
    <div className="space-y-6">
      <PageHeader
        icon={<FileText size={22} />}
        title="PDF report"
        description="Choose a rendering engine and page layout, then generate any of its sample documents."
      />

      <section className="panel p-5 animate-rise">
        <h2 className="text-lg font-semibold">Rendering engine</h2>
        <div className="mt-4 grid gap-3 sm:grid-cols-3" role="radiogroup" aria-label="Rendering engine">
          {ENGINES.map((e) => {
            const m = GENERATOR_META[e.key]
            const on = driver === e.key
            const ready = health?.engines.find((h) => h.key === e.key)?.ready
            return (
              <button
                key={e.key}
                type="button"
                role="radio"
                aria-checked={on}
                onClick={() => setDriver(e.key)}
                className="relative overflow-hidden rounded-xl border p-4 text-left transition-all duration-300 hover:-translate-y-0.5"
                style={on ? { borderColor: m.color, background: `${m.color}12`, boxShadow: `0 0 28px -10px ${m.color}` } : { borderColor: 'rgb(var(--line))' }}
              >
                <div className="flex items-center justify-between">
                  <div className="flex h-9 w-9 items-center justify-center rounded-lg" style={{ background: `${m.color}20`, color: m.color }}>
                    <m.icon size={18} />
                  </div>
                  {on ? <Check size={16} style={{ color: m.color }} /> : ready === false && <span className="chip !text-warn">Not installed</span>}
                </div>
                <div className="mt-3 font-semibold">{e.label}</div>
                <div className="mt-0.5 text-sm text-mute">{e.blurb}</div>
              </button>
            )
          })}
        </div>
      </section>

      <section className="panel p-5 animate-rise" style={{ animationDelay: '.07s' }}>
        <h2 className="text-lg font-semibold">Page layout</h2>
        <p className="hint mt-1">Leave on "Sample default" to use each document's own layout.</p>
        <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:max-w-xl">
          <div>
            <label htmlFor="paper" className="label">Paper size</label>
            <select id="paper" className="field" value={paper} onChange={(e) => setPaper(e.target.value as PaperSize | '')}>
              <option value="">Sample default</option>
              {PAPER.map((p) => <option key={p}>{p}</option>)}
            </select>
          </div>
          <div>
            <label htmlFor="orient" className="label">Orientation</label>
            <select id="orient" className="field" value={orientation} onChange={(e) => setOrientation(e.target.value as Orientation | '')}>
              <option value="">Sample default</option>
              <option value="portrait">Portrait</option>
              <option value="landscape">Landscape</option>
            </select>
          </div>
        </div>
      </section>

      <section>
        <h2 className="mb-3 text-lg font-semibold">Documents for {GENERATOR_META[driver].label}</h2>
        <div key={driver} className="stagger grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          {samples.map((s) => (
            <SampleCard key={s.id + paper + orientation} sample={s} overrides={overrides} disabledReason={disabledReason} />
          ))}
        </div>
      </section>
    </div>
  )
}
