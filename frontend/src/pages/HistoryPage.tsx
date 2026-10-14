import { useCallback, useEffect, useState } from 'react'
import { Download, History, Trash2 } from 'lucide-react'
import { api, downloadUrl, errorMessage, formatBytes } from '../lib/api'
import { TYPE_META } from '../lib/meta'
import type { ReportRow } from '../types/report'
import PageHeader from '../components/PageHeader'

interface Page {
  data: ReportRow[]
  current_page: number
  last_page: number
  total: number
}

const FILTERS = ['all', 'pdf', 'excel', 'csv', 'docx'] as const

export default function HistoryPage() {
  const [filter, setFilter] = useState<(typeof FILTERS)[number]>('all')
  const [page, setPage] = useState(1)
  const [data, setData] = useState<Page | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [removing, setRemoving] = useState<number | null>(null)

  const load = useCallback(() => {
    api
      .get<Page>('/reports', { params: { page, ...(filter !== 'all' ? { type: filter } : {}) } })
      .then((r) => setData(r.data))
      .catch((e) => setError(errorMessage(e, 'Could not load history.')))
  }, [filter, page])

  useEffect(load, [load])

  async function remove(r: ReportRow) {
    if (!window.confirm(`Delete "${r.title}"? The file will be removed from disk.`)) return
    setRemoving(r.id)
    try {
      await api.delete(`/reports/${r.id}`)
      load()
    } catch (e) {
      setError(errorMessage(e, 'Could not delete the report.'))
    } finally {
      setRemoving(null)
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader icon={<History size={22} />} title="History" description="Every file the system has generated. Download it again or delete it." />

      <div className="flex flex-wrap gap-2" role="tablist" aria-label="Filter by type">
        {FILTERS.map((f) => (
          <button
            key={f}
            role="tab"
            aria-selected={filter === f}
            onClick={() => {
              setFilter(f)
              setPage(1)
            }}
            className={`rounded-lg border px-3 py-1.5 text-sm transition-all ${
              filter === f ? 'border-accent bg-accent/10 text-accent' : 'border-line text-mute hover:border-accent/40 hover:text-ink'
            }`}
          >
            {f === 'all' ? 'All' : TYPE_META[f].label}
          </button>
        ))}
      </div>

      {error && (
        <div className="rounded-xl border border-bad/40 bg-bad/10 px-4 py-3 text-sm text-bad" role="alert">
          {error}
        </div>
      )}

      <div className="panel overflow-hidden">
        {!data ? (
          <div className="space-y-2 p-4">
            {[0, 1, 2, 3].map((i) => (
              <div key={i} className="skeleton h-10" />
            ))}
          </div>
        ) : data.data.length === 0 ? (
          <div className="px-5 py-12 text-center text-sm text-mute">No reports match this filter yet.</div>
        ) : (
          <ul className="divide-y divide-line">
            {data.data.map((r) => (
              <li key={r.id} className="flex flex-wrap items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-accent/5 sm:px-5">
                <div className="flex min-w-0 items-center gap-3">
                  <span className="h-8 w-1 shrink-0 rounded-full" style={{ background: TYPE_META[r.type]?.color }} />
                  <div className="min-w-0">
                    <div className="truncate font-medium">{r.title}</div>
                    <div className="truncate font-mono text-xs text-mute">{r.filename}</div>
                  </div>
                </div>
                <div className="flex items-center gap-3 text-sm text-mute">
                  <span className="chip">
                    {TYPE_META[r.type]?.label}
                    {r.driver ? ` · ${r.driver}` : ''}
                  </span>
                  <span className="hidden font-mono text-xs sm:inline">{formatBytes(r.size_bytes)}</span>
                  <span className="hidden text-xs md:inline">{new Date(r.created_at).toLocaleString()}</span>
                  <a href={downloadUrl(r.filename)} className="btn-ghost h-8 w-8 !p-0" aria-label={`Download ${r.title}`}>
                    <Download size={14} />
                  </a>
                  <button onClick={() => remove(r)} disabled={removing === r.id} className="btn-ghost h-8 w-8 !p-0 hover:!border-bad/60 hover:!bg-bad/10 hover:text-bad" aria-label={`Delete ${r.title}`}>
                    <Trash2 size={14} />
                  </button>
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>

      {data && data.last_page > 1 && (
        <div className="flex items-center justify-center gap-3 text-sm">
          <button className="btn-ghost" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
            Previous
          </button>
          <span className="text-mute tabular-nums">
            Page {data.current_page} of {data.last_page}
          </span>
          <button className="btn-ghost" disabled={page >= data.last_page} onClick={() => setPage((p) => p + 1)}>
            Next
          </button>
        </div>
      )}
    </div>
  )
}
