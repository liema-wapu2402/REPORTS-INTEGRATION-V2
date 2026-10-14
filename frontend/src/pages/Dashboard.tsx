import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, Boxes, AlertTriangle, Download } from 'lucide-react'
import { api, downloadUrl, errorMessage, formatBytes } from '../lib/api'
import { TYPE_META } from '../lib/meta'
import type { DashboardSummary } from '../types/report'
import PipelineHero from '../components/PipelineHero'
import CountUp from '../components/CountUp'

const DRIVER_COLORS: Record<string, string> = { dompdf: '#fb7185', snappy: '#fb923c', puppeteer: '#c084fc' }

export default function Dashboard() {
  const [summary, setSummary] = useState<DashboardSummary | null>(null)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    api
      .get<DashboardSummary>('/dashboard/summary')
      .then((res) => setSummary(res.data))
      .catch((e) => setError(errorMessage(e, 'Could not load the dashboard. Run `php artisan migrate` and restart the backend.')))
  }, [])

  const maxDay = Math.max(1, ...(summary?.activity.map((a) => a.count) ?? [1]))
  const maxDriver = Math.max(1, ...Object.values(summary?.by_driver ?? { x: 1 }))

  return (
    <div className="space-y-6 sm:space-y-8">
      {/* Hero */}
      <section className="panel relative overflow-hidden p-6 sm:p-8 animate-rise">
        <div className="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-accent/10 blur-3xl" aria-hidden />
        <div className="relative grid items-center gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
          <div>
            <h1 className="text-3xl font-bold leading-[1.1] sm:text-4xl lg:text-5xl">
              Every report format, <span className="text-gradient">one pipeline.</span>
            </h1>
            <p className="mt-4 max-w-md text-mute">
              Generate PDF, Excel, CSV and Word files from the same dashboard. Pick an engine, pick a sample, and download in seconds.
            </p>
            <div className="mt-6 flex flex-wrap gap-3">
              <Link to="/samples" className="btn-primary">
                <Boxes size={16} /> Browse sample reports
              </Link>
              <Link to="/reports/pdf" className="btn-ghost">
                Build a custom PDF <ArrowRight size={15} />
              </Link>
            </div>
          </div>
          <PipelineHero />
        </div>
      </section>

      {error && (
        <div className="flex items-start gap-3 rounded-xl border border-warn/40 bg-warn/10 px-4 py-3 text-sm text-warn" role="alert">
          <AlertTriangle size={16} className="mt-0.5 shrink-0" /> {error}
        </div>
      )}

      {/* Counters */}
      <section className="stagger grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 xl:grid-cols-5">
        {Object.entries(TYPE_META).map(([type, meta]) => (
          <div key={type} className="panel panel-hover relative overflow-hidden p-4 sm:p-5">
            <div className="absolute inset-x-0 top-0 h-0.5" style={{ background: meta.color }} />
            <div className="font-display text-3xl font-semibold tabular-nums">
              <CountUp value={summary?.by_type[type as keyof DashboardSummary['by_type']] ?? 0} />
            </div>
            <div className="mt-1 text-sm text-mute">{meta.label} reports</div>
          </div>
        ))}
      </section>

      <section className="grid gap-4 lg:grid-cols-3 sm:gap-6">
        {/* Activity */}
        <div className="panel p-5 lg:col-span-2">
          <div className="flex items-baseline justify-between">
            <h2 className="text-lg font-semibold">Reports in the last 7 days</h2>
            <span className="text-sm text-mute">
              {summary ? `${summary.total_reports} total · ${formatBytes(summary.total_size_bytes)}` : ''}
            </span>
          </div>
          <div className="mt-6 flex h-40 items-end gap-2 sm:gap-4" role="img" aria-label="Bar chart of reports generated per day">
            {(summary?.activity ?? Array.from({ length: 7 }, (_, i) => ({ date: String(i), label: '', count: 0 }))).map((d) => (
              <div key={d.date} className="group flex h-full flex-1 flex-col items-center justify-end gap-2">
                <span className="text-xs tabular-nums text-mute opacity-0 transition-opacity group-hover:opacity-100">{d.count}</span>
                <div
                  className="w-full rounded-t-md bg-gradient-to-t from-accent/30 to-accent transition-all duration-700 group-hover:brightness-125"
                  style={{ height: `${Math.max(4, (d.count / maxDay) * 100)}%` }}
                />
                <span className="text-xs text-mute">{d.label}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Engines */}
        <div className="panel p-5">
          <h2 className="text-lg font-semibold">PDF engine usage</h2>
          <div className="mt-5 space-y-4">
            {(['dompdf', 'snappy', 'puppeteer'] as const).map((d) => {
              const n = summary?.by_driver[d] ?? 0
              return (
                <div key={d}>
                  <div className="mb-1.5 flex justify-between text-sm">
                    <span>{d === 'dompdf' ? 'DomPDF' : d === 'snappy' ? 'Snappy' : 'Puppeteer'}</span>
                    <span className="tabular-nums text-mute">{n}</span>
                  </div>
                  <div className="h-2 overflow-hidden rounded-full bg-line/60">
                    <div className="h-full rounded-full transition-all duration-700" style={{ width: `${(n / maxDriver) * 100}%`, background: DRIVER_COLORS[d] }} />
                  </div>
                </div>
              )
            })}
          </div>
        </div>
      </section>

      {/* Recent */}
      <section className="panel overflow-hidden">
        <div className="flex items-center justify-between border-b border-line px-5 py-4">
          <h2 className="text-lg font-semibold">Recently generated</h2>
          <Link to="/history" className="text-sm text-accent hover:underline">
            View all
          </Link>
        </div>
        {summary?.recent.length ? (
          <ul className="divide-y divide-line">
            {summary.recent.slice(0, 6).map((r) => (
              <li key={r.id} className="flex items-center justify-between gap-3 px-5 py-3 text-sm transition-colors hover:bg-accent/5">
                <div className="flex min-w-0 items-center gap-3">
                  <span className="h-2 w-2 shrink-0 rounded-full" style={{ background: TYPE_META[r.type]?.color }} />
                  <span className="truncate font-medium">{r.title}</span>
                  <span className="hidden text-mute sm:inline">{TYPE_META[r.type]?.label}{r.driver ? ` · ${r.driver}` : ''}</span>
                </div>
                <div className="flex shrink-0 items-center gap-3 text-mute">
                  <span className="font-mono text-xs">{formatBytes(r.size_bytes)}</span>
                  <a href={downloadUrl(r.filename)} className="btn-ghost h-8 w-8 !p-0" aria-label={`Download ${r.title}`}>
                    <Download size={14} />
                  </a>
                </div>
              </li>
            ))}
          </ul>
        ) : (
          <div className="px-5 py-10 text-center text-sm text-mute">
            Nothing generated yet.{' '}
            <Link to="/samples" className="text-accent hover:underline">
              Generate your first sample report
            </Link>
            .
          </div>
        )}
      </section>
    </div>
  )
}
