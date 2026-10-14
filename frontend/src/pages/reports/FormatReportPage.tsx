import { FormEvent, ReactNode, useState } from 'react'
import { AlertCircle, Check, Download, Loader2, Sparkles } from 'lucide-react'
import { api, errorMessage, formatBytes, triggerDownload, type ReportResponse } from '../../lib/api'
import type { SamplePreview } from '../../types/report'
import PageHeader from '../../components/PageHeader'
import TableEditor, { TableData } from '../../components/TableEditor'

interface Props {
  kind: 'excel' | 'csv' | 'docx'
  title: string
  description: string
  icon: ReactNode
  /** Sample datasets (catalog ids) the user can load into the editor. */
  loadFrom: { id: string; label: string }[]
  withNarrative?: boolean
}

const STARTER: TableData = {
  headings: ['Name', 'Department', 'Score'],
  rows: [
    ['Juan Dela Cruz', 'Records', '95'],
    ['Maria Santos', 'IT', '88'],
  ],
}

/** Shared editor + generate flow for the Excel, CSV and Word report pages. */
export default function FormatReportPage({ kind, title, description, icon, loadFrom, withNarrative }: Props) {
  const [reportTitle, setReportTitle] = useState(`${title} ${new Date().toLocaleDateString()}`)
  const [table, setTable] = useState<TableData>(STARTER)
  const [narrative, setNarrative] = useState('This report was generated from the data below.')
  const [loading, setLoading] = useState(false)
  const [loadingSample, setLoadingSample] = useState(false)
  const [result, setResult] = useState<ReportResponse | null>(null)
  const [error, setError] = useState<string | null>(null)

  async function loadSample(id: string) {
    if (!id) return
    setLoadingSample(true)
    setError(null)
    try {
      const res = await api.get<SamplePreview>(`/samples/${id}/preview`, { params: {} })
      const headings = res.data.headings.map((h) => h.replace(/_/g, ' '))
      const rows = res.data.rows.map((r) => res.data.headings.map((h) => String(r[h])))
      setTable({ headings, rows })
    } catch (e) {
      setError(errorMessage(e, 'Could not load the sample data.'))
    } finally {
      setLoadingSample(false)
    }
  }

  async function submit(e: FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError(null)
    setResult(null)
    try {
      const rows = table.rows.map((r) => r.map((v) => (v.trim() !== '' && !isNaN(Number(v)) ? Number(v) : v)))
      const res = await api.post<ReportResponse>(`/reports/${kind}`, {
        title: reportTitle,
        headings: table.headings,
        rows,
        ...(withNarrative ? { paragraphs: narrative.split(/\n{2,}/).filter(Boolean) } : {}),
      })
      setResult(res.data)
      triggerDownload(res.data.download_url, res.data.filename)
    } catch (err) {
      setError(errorMessage(err, 'Generation failed. Check the backend logs.'))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader icon={icon} title={title} description={description} />

      <form onSubmit={submit} className="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div className="min-w-0 space-y-5">
          <div className="panel space-y-4 p-5 animate-rise">
            <div>
              <label htmlFor="rt" className="label">Report title</label>
              <input id="rt" className="field" value={reportTitle} onChange={(e) => setReportTitle(e.target.value)} required />
            </div>
            {withNarrative && (
              <div>
                <label htmlFor="nar" className="label">Introduction</label>
                <textarea
                  id="nar"
                  rows={3}
                  value={narrative}
                  onChange={(e) => setNarrative(e.target.value)}
                  className="w-full rounded-lg border border-line bg-bg/60 px-3 py-2 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/25"
                />
                <p className="hint mt-1">Leave a blank line to start a new paragraph.</p>
              </div>
            )}
          </div>

          <div className="panel p-5 animate-rise" style={{ animationDelay: '.08s' }}>
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
              <h2 className="text-lg font-semibold">Data</h2>
              <div className="flex items-center gap-2">
                {loadingSample ? <Loader2 size={15} className="animate-spin text-accent" /> : <Sparkles size={15} className="text-accent" />}
                <select aria-label="Load sample data" className="field !h-9 !w-auto" defaultValue="" onChange={(e) => { loadSample(e.target.value); e.target.value = '' }}>
                  <option value="">Load sample data…</option>
                  {loadFrom.map((o) => (
                    <option key={o.id} value={o.id}>{o.label}</option>
                  ))}
                </select>
              </div>
            </div>
            <TableEditor value={table} onChange={setTable} />
          </div>
        </div>

        <aside className="space-y-4 xl:sticky xl:top-24 xl:self-start">
          <div className="panel p-5 animate-rise" style={{ animationDelay: '.14s' }}>
            <div className="text-sm text-mute">
              {table.rows.length} rows · {table.headings.length} columns
            </div>
            <button type="submit" disabled={loading || !table.rows.length} className="btn-primary mt-4 w-full">
              {loading ? <Loader2 size={16} className="animate-spin" /> : <Download size={16} />}
              {loading ? 'Generating…' : `Generate ${title.replace(' report', '')}`}
            </button>
            {!table.rows.length && <p className="hint mt-2">Add at least one row.</p>}
          </div>

          {error && (
            <div className="flex items-start gap-2 rounded-xl border border-bad/40 bg-bad/10 px-4 py-3 text-sm text-bad animate-rise" role="alert">
              <AlertCircle size={16} className="mt-0.5 shrink-0" /> {error}
            </div>
          )}

          {result && (
            <div className="rounded-xl border border-ok/40 bg-ok/10 px-4 py-3 text-sm animate-rise">
              <div className="flex items-center gap-2 font-medium text-ok">
                <Check size={16} /> {result.message}
              </div>
              <div className="mt-1 break-all font-mono text-xs text-mute">{result.filename}</div>
              <div className="mt-2 flex items-center justify-between text-xs text-mute">
                <span>{result.size_bytes != null && formatBytes(result.size_bytes)}</span>
                <a href={result.download_url} className="text-accent hover:underline">Download again</a>
              </div>
            </div>
          )}
        </aside>
      </form>
    </div>
  )
}
