import { useEffect, useState } from 'react'
import { useLocation } from 'react-router-dom'
import { CheckCircle2, CircleAlert, RefreshCw, Database, Server, MonitorSmartphone } from 'lucide-react'
import { api, formatBytes } from '../lib/api'
import { GENERATOR_META } from '../lib/meta'
import type { AboutInfo, SystemHealth } from '../types/report'
import Logo, { LogoMark } from '../components/Logo'

const FEATURES = [
  { title: 'Three PDF engines', body: 'Switch between DomPDF, Snappy (wkhtmltopdf) and Puppeteer (Chromium) depending on the layout you need.' },
  { title: 'Office formats', body: 'Styled Excel workbooks with multiple sheets and totals, plain CSV exports, and editable Word documents.' },
  { title: 'Sample gallery', body: '14 ready-made reports across six generators. Preview the data, generate the file, compare the output.' },
  { title: 'Report history', body: 'Every generated file is logged in the database so it can be downloaded again or removed from disk.' },
  { title: 'Engine health check', body: 'See which generators are installed on this machine and the exact fix for the ones that are not.' },
  { title: 'Light and dark themes', body: 'The interface adapts to screen size and respects the reduced-motion setting of your device.' },
]

const FLOW = [
  { title: 'Choose', body: 'Pick a sample from the gallery or build a custom report.' },
  { title: 'Request', body: 'The React app sends the request to the Laravel API.' },
  { title: 'Render', body: 'The matching engine builds the PDF, workbook, CSV or document.' },
  { title: 'Deliver', body: 'The file is stored, logged in history and downloaded.' },
]

export default function AboutPage() {
  const [about, setAbout] = useState<AboutInfo | null>(null)
  const [health, setHealth] = useState<SystemHealth | null>(null)
  const [offline, setOffline] = useState(false)
  const [checking, setChecking] = useState(false)
  const { hash } = useLocation()

  const check = () => {
    setChecking(true)
    api
      .get<SystemHealth>('/system/health')
      .then((r) => {
        setHealth(r.data)
        setOffline(false)
      })
      .catch(() => setOffline(true))
      .finally(() => setChecking(false))
  }

  useEffect(() => {
    api.get<AboutInfo>('/system/about').then((r) => setAbout(r.data)).catch(() => setOffline(true))
    check()
  }, [])

  useEffect(() => {
    if (hash) document.querySelector(hash)?.scrollIntoView({ behavior: 'smooth' })
  }, [hash, health])

  return (
    <div className="space-y-8 sm:space-y-10">
      {/* Identity */}
      <section className="panel relative overflow-hidden p-6 sm:p-10 animate-rise">
        <div className="pointer-events-none absolute -left-16 -top-16 h-64 w-64 rounded-full bg-accent/15 blur-3xl" aria-hidden />
        <div className="relative flex flex-col items-start gap-6 sm:flex-row sm:items-center">
          <div className="relative">
            <div className="absolute inset-0 animate-ring rounded-full bg-accent/30" aria-hidden />
            <LogoMark size={112} className="relative" />
          </div>
          <div>
            <h1 className="text-3xl font-bold sm:text-4xl">
              Report <span className="text-gradient">Integrations</span>
            </h1>
            <p className="mt-2 max-w-xl text-mute">
              {about?.tagline ?? 'One dashboard. Every report format.'} A single place to generate PDF, Excel, CSV and Word reports, built on a Laravel API and a React interface.
            </p>
            <div className="mt-4 flex flex-wrap gap-2">
              <span className="chip font-mono">v{about?.version ?? '2.0.0'}</span>
              <span className="chip">By {about?.owner ?? 'AVANT GUARD'}</span>
              {about && <span className="chip">{about.totals.reports_generated} reports · {formatBytes(about.totals.storage_bytes)}</span>}
            </div>
          </div>
        </div>
      </section>

      {/* How it works */}
      <section>
        <h2 className="mb-4 text-xl font-semibold">How it works</h2>
        <ol className="stagger grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {FLOW.map((s, i) => (
            <li key={s.title} className="panel relative p-5">
              <div className="mb-3 flex h-8 w-8 items-center justify-center rounded-full border border-accent/40 bg-accent/10 font-mono text-sm text-accent">{i + 1}</div>
              <h3 className="font-semibold">{s.title}</h3>
              <p className="mt-1 text-sm text-mute">{s.body}</p>
              {i < FLOW.length - 1 && <div className="absolute -right-2 top-9 hidden h-px w-4 bg-accent/50 lg:block" aria-hidden />}
            </li>
          ))}
        </ol>
      </section>

      {/* Features */}
      <section>
        <h2 className="mb-4 text-xl font-semibold">What it does</h2>
        <div className="stagger grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {FEATURES.map((f) => (
            <div key={f.title} className="panel panel-hover p-5">
              <h3 className="font-semibold">{f.title}</h3>
              <p className="mt-1.5 text-sm text-mute">{f.body}</p>
            </div>
          ))}
        </div>
      </section>

      {/* System status */}
      <section id="status" className="scroll-mt-24">
        <div className="mb-4 flex items-center justify-between gap-3">
          <h2 className="text-xl font-semibold">System status</h2>
          <button onClick={check} className="btn-ghost" disabled={checking}>
            <RefreshCw size={14} className={checking ? 'animate-spin' : ''} /> Recheck
          </button>
        </div>

        {offline && (
          <div className="mb-4 rounded-xl border border-bad/40 bg-bad/10 px-4 py-3 text-sm text-bad" role="alert">
            The backend is not reachable. Start it with <code className="font-mono">php artisan serve</code> and recheck.
          </div>
        )}

        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {(health?.engines ?? []).map((e) => {
            const m = GENERATOR_META[e.key]
            return (
              <div key={e.key} className="panel flex items-start gap-3 p-4">
                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" style={{ background: `${m.color}18`, color: m.color }}>
                  <m.icon size={17} />
                </div>
                <div className="min-w-0 flex-1">
                  <div className="flex items-center justify-between gap-2">
                    <span className="font-medium">{e.label}</span>
                    {e.ready ? (
                      <span className="flex items-center gap-1 text-xs text-ok"><CheckCircle2 size={14} /> Ready</span>
                    ) : (
                      <span className="flex items-center gap-1 text-xs text-warn"><CircleAlert size={14} /> Not installed</span>
                    )}
                  </div>
                  {e.fix && <p className="mt-1 break-words font-mono text-xs text-mute">{e.fix}</p>}
                </div>
              </div>
            )
          })}
          {!health && !offline && [0, 1, 2].map((i) => <div key={i} className="skeleton h-20" />)}
        </div>

        {health && (
          <div className="mt-3 flex items-center gap-2 text-sm text-mute">
            <Database size={15} /> Database: {health.database ? <span className="text-ok">connected</span> : <span className="text-bad">not reachable. Run php artisan migrate.</span>}
          </div>
        )}
      </section>

      {/* Stack */}
      <section>
        <h2 className="mb-4 text-xl font-semibold">Built with</h2>
        <div className="grid gap-3 sm:grid-cols-2">
          <div className="panel flex items-start gap-3 p-5">
            <Server className="mt-0.5 text-accent" size={20} />
            <div>
              <h3 className="font-semibold">Backend</h3>
              <p className="mt-1 text-sm text-mute">{about?.stack.backend ?? 'Laravel · PHP'}</p>
              <p className="mt-1 text-sm text-mute">DomPDF, Snappy, Browsershot, Maatwebsite Excel, PhpWord</p>
            </div>
          </div>
          <div className="panel flex items-start gap-3 p-5">
            <MonitorSmartphone className="mt-0.5 text-accent2" size={20} />
            <div>
              <h3 className="font-semibold">Frontend</h3>
              <p className="mt-1 text-sm text-mute">{about?.stack.frontend ?? 'React · TypeScript · Vite · Tailwind CSS'}</p>
              <p className="mt-1 text-sm text-mute">Responsive layout, animated SVG, light and dark themes</p>
            </div>
          </div>
        </div>
      </section>

      <footer className="flex flex-col items-center gap-3 border-t border-line pt-8 text-center text-sm text-mute">
        <Logo size={30} />
        <p>© {new Date().getFullYear()} AVANT GUARD. All rights reserved.</p>
      </footer>
    </div>
  )
}
