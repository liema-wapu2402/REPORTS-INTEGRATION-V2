import { useCallback, useEffect, useMemo, useState } from 'react'
import { Boxes, Loader2, Zap } from 'lucide-react'
import { api, errorMessage, triggerDownload, type ReportResponse } from '../lib/api'
import { GENERATOR_META } from '../lib/meta'
import type { GeneratorInfo, GeneratorKey, SystemHealth } from '../types/report'
import PageHeader from '../components/PageHeader'
import SampleCard from '../components/SampleCard'

export default function SamplesPage() {
  const [generators, setGenerators] = useState<GeneratorInfo[]>([])
  const [health, setHealth] = useState<SystemHealth | null>(null)
  const [active, setActive] = useState<GeneratorKey>('dompdf')
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)
  const [runAll, setRunAll] = useState<{ done: number; total: number } | null>(null)

  const load = useCallback(() => {
    api
      .get<{ generators: GeneratorInfo[] }>('/samples')
      .then((r) => setGenerators(r.data.generators))
      .catch((e) => setError(errorMessage(e, 'Could not load the sample catalog.')))
      .finally(() => setLoading(false))
    api.get<SystemHealth>('/system/health').then((r) => setHealth(r.data)).catch(() => undefined)
  }, [])

  useEffect(load, [load])

  const current = useMemo(() => generators.find((g) => g.key === active), [generators, active])
  const engine = health?.engines.find((e) => e.key === active)
  const disabledReason = engine && !engine.ready ? `Engine not ready. ${engine.fix ?? ''}` : null

  async function generateAll() {
    if (!current) return
    setRunAll({ done: 0, total: current.samples.length })
    for (const [i, s] of current.samples.entries()) {
      try {
        const res = await api.post<ReportResponse>(`/samples/${s.id}/generate`)
        triggerDownload(res.data.download_url, res.data.filename)
      } catch (e) {
        setError(errorMessage(e, `Could not generate ${s.name}.`))
        break
      }
      setRunAll({ done: i + 1, total: current.samples.length })
      await new Promise((r) => setTimeout(r, 350)) // let the browser queue multiple downloads
    }
    setTimeout(() => setRunAll(null), 1200)
  }

  return (
    <div className="space-y-6">
      <PageHeader
        icon={<Boxes size={22} />}
        title="Sample gallery"
        description="Ready-made reports for every generator. Preview the data, generate a file, and compare how each engine renders it."
      />

      {/* Generator tabs */}
      <div role="tablist" aria-label="Report generators" className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0">
        {generators.map((g) => {
          const m = GENERATOR_META[g.key]
          const selected = g.key === active
          const h = health?.engines.find((e) => e.key === g.key)
          return (
            <button
              key={g.key}
              role="tab"
              aria-selected={selected}
              onClick={() => setActive(g.key)}
              className={`flex shrink-0 items-center gap-2 rounded-lg border px-3.5 py-2 text-sm font-medium transition-all duration-200 ${
                selected ? 'text-ink' : 'border-line text-mute hover:border-accent/40 hover:text-ink'
              }`}
              style={selected ? { borderColor: m.color, background: `${m.color}18`, boxShadow: `0 0 24px -8px ${m.color}` } : undefined}
            >
              <m.icon size={16} style={{ color: m.color }} />
              {m.label}
              <span className="rounded-full bg-line/60 px-1.5 text-xs tabular-nums text-mute">{g.samples.length}</span>
              {h && <span className={`h-1.5 w-1.5 rounded-full ${h.ready ? 'bg-ok' : 'bg-warn'}`} title={h.ready ? 'Ready' : 'Not installed'} />}
            </button>
          )
        })}
      </div>

      {error && (
        <div className="rounded-xl border border-bad/40 bg-bad/10 px-4 py-3 text-sm text-bad" role="alert">
          {error}
        </div>
      )}

      {loading && (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          {[0, 1, 2].map((i) => (
            <div key={i} className="skeleton h-48" />
          ))}
        </div>
      )}

      {current && (
        <div key={current.key} className="space-y-4">
          <div className="flex flex-wrap items-center justify-between gap-3 animate-rise">
            <p className="max-w-2xl text-sm text-mute">{current.blurb}</p>
            <button onClick={generateAll} disabled={!!runAll || !!disabledReason || current.samples.length < 2} className="btn-ghost">
              {runAll ? <Loader2 size={15} className="animate-spin" /> : <Zap size={15} />}
              {runAll ? `Generating ${runAll.done}/${runAll.total}` : `Generate all ${current.samples.length}`}
            </button>
          </div>
          <div className="stagger grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {current.samples.map((s) => (
              <SampleCard key={s.id} sample={s} disabledReason={disabledReason} />
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
