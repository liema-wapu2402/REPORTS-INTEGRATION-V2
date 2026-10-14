import { useState } from 'react'
import { Download, Eye, EyeOff, Loader2, Play, AlertCircle, Check } from 'lucide-react'
import { api, errorMessage, formatBytes, triggerDownload, type ReportResponse } from '../lib/api'
import { GENERATOR_META } from '../lib/meta'
import type { SampleInfo, SamplePreview } from '../types/report'

interface Props {
  sample: SampleInfo
  disabledReason?: string | null
  /** Optional overrides for PDF samples (used by the PDF page). */
  overrides?: { paper?: string; orientation?: string }
  onGenerated?: () => void
}

export default function SampleCard({ sample, disabledReason, overrides, onGenerated }: Props) {
  const meta = GENERATOR_META[sample.generator]
  const Icon = meta.icon
  const [status, setStatus] = useState<'idle' | 'busy' | 'done' | 'error'>('idle')
  const [result, setResult] = useState<ReportResponse | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [preview, setPreview] = useState<SamplePreview | null>(null)
  const [showPreview, setShowPreview] = useState(false)
  const [loadingPreview, setLoadingPreview] = useState(false)

  async function togglePreview() {
    if (showPreview) return setShowPreview(false)
    setShowPreview(true)
    if (preview) return
    setLoadingPreview(true)
    try {
      const res = await api.get<SamplePreview>(`/samples/${sample.id}/preview`)
      setPreview(res.data)
    } catch (e) {
      setError(errorMessage(e, 'Could not load the data preview.'))
    } finally {
      setLoadingPreview(false)
    }
  }

  async function generate() {
    setStatus('busy')
    setError(null)
    try {
      const res = await api.post<ReportResponse>(`/samples/${sample.id}/generate`, overrides ?? {})
      setResult(res.data)
      setStatus('done')
      triggerDownload(res.data.download_url, res.data.filename)
      onGenerated?.()
    } catch (e) {
      setError(errorMessage(e, 'Generation failed. Check the backend logs.'))
      setStatus('error')
    }
  }

  return (
    <article
      className="panel panel-hover group relative flex flex-col overflow-hidden p-5"
      style={{ ['--c' as string]: meta.color }}
    >
      <div className="absolute inset-x-0 top-0 h-0.5 origin-left scale-x-0 transition-transform duration-500 group-hover:scale-x-100" style={{ background: meta.color }} />
      {status === 'busy' && <div className="absolute inset-x-0 top-0 h-0.5 animate-shimmer bg-[length:200%_100%]" style={{ backgroundImage: `linear-gradient(90deg,transparent,${meta.color},transparent)` }} />}

      <div className="flex items-start gap-3">
        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border" style={{ borderColor: `${meta.color}55`, background: `${meta.color}14`, color: meta.color }}>
          <Icon size={19} />
        </div>
        <div className="min-w-0">
          <h3 className="text-base font-semibold leading-tight">{sample.name}</h3>
          <p className="mt-1 text-sm text-mute">{sample.description}</p>
        </div>
      </div>

      <div className="mt-3 flex flex-wrap gap-1.5">
        {sample.paper && (
          <span className="chip font-mono">
            {sample.paper.toUpperCase()} · {sample.orientation}
          </span>
        )}
        {sample.tags.map((t) => (
          <span key={t} className="chip">
            {t}
          </span>
        ))}
      </div>

      <div className="mt-4 flex flex-wrap items-center gap-2">
        <button onClick={generate} disabled={status === 'busy' || !!disabledReason} className="btn-primary" title={disabledReason ?? undefined}>
          {status === 'busy' ? <Loader2 size={15} className="animate-spin" /> : <Play size={14} />}
          {status === 'busy' ? 'Generating…' : 'Generate'}
        </button>
        <button onClick={togglePreview} className="btn-ghost" aria-expanded={showPreview}>
          {showPreview ? <EyeOff size={15} /> : <Eye size={15} />} Data
        </button>
      </div>

      {disabledReason && <p className="mt-2 text-xs text-warn">{disabledReason}</p>}

      {status === 'done' && result && (
        <div className="mt-3 flex items-center justify-between gap-2 rounded-lg border border-ok/30 bg-ok/10 px-3 py-2 text-sm animate-rise">
          <span className="flex min-w-0 items-center gap-2 text-ok">
            <Check size={15} className="shrink-0" />
            <span className="truncate font-mono text-xs">{result.filename}</span>
          </span>
          <span className="flex shrink-0 items-center gap-3 text-xs text-mute">
            {result.size_bytes != null && formatBytes(result.size_bytes)}
            {result.elapsed_ms != null && <span>{result.elapsed_ms} ms</span>}
            <a href={result.download_url} className="text-accent hover:underline" aria-label="Download again">
              <Download size={14} />
            </a>
          </span>
        </div>
      )}

      {status === 'error' && error && (
        <div className="mt-3 flex items-start gap-2 rounded-lg border border-bad/30 bg-bad/10 px-3 py-2 text-sm text-bad animate-rise" role="alert">
          <AlertCircle size={15} className="mt-0.5 shrink-0" /> {error}
        </div>
      )}

      <div className={`grid transition-all duration-300 ${showPreview ? 'mt-4 grid-rows-[1fr]' : 'grid-rows-[0fr]'}`}>
        <div className="min-h-0 overflow-hidden">
          {loadingPreview && <div className="skeleton h-24 w-full" />}
          {preview && (
            <div className="overflow-x-auto rounded-lg border border-line">
              <table className="w-full text-left text-xs">
                <thead className="bg-line/40 text-mute">
                  <tr>
                    {preview.headings.map((h) => (
                      <th key={h} className="whitespace-nowrap px-2.5 py-1.5 font-medium">
                        {h.replace(/_/g, ' ')}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody className="font-mono">
                  {preview.rows.slice(0, 5).map((row, i) => (
                    <tr key={i} className="border-t border-line">
                      {preview.headings.map((h) => (
                        <td key={h} className="whitespace-nowrap px-2.5 py-1.5">
                          {String(row[h])}
                        </td>
                      ))}
                    </tr>
                  ))}
                </tbody>
              </table>
              <div className="border-t border-line px-2.5 py-1.5 text-mute">
                Showing 5 of {preview.total_rows} rows
              </div>
            </div>
          )}
        </div>
      </div>
    </article>
  )
}
