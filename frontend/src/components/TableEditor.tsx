import { Plus, Trash2 } from 'lucide-react'

export interface TableData {
  headings: string[]
  rows: string[][]
}

interface Props {
  value: TableData
  onChange: (next: TableData) => void
}

/** Small spreadsheet-like editor: edit headings and cells, add or remove rows and columns. */
export default function TableEditor({ value, onChange }: Props) {
  const { headings, rows } = value

  const setHeading = (c: number, v: string) => onChange({ headings: headings.map((h, i) => (i === c ? v : h)), rows })
  const setCell = (r: number, c: number, v: string) =>
    onChange({ headings, rows: rows.map((row, i) => (i === r ? row.map((cell, j) => (j === c ? v : cell)) : row)) })
  const addRow = () => onChange({ headings, rows: [...rows, headings.map(() => '')] })
  const removeRow = (r: number) => onChange({ headings, rows: rows.filter((_, i) => i !== r) })
  const addCol = () => onChange({ headings: [...headings, `Column ${headings.length + 1}`], rows: rows.map((row) => [...row, '']) })
  const removeCol = (c: number) =>
    onChange({ headings: headings.filter((_, i) => i !== c), rows: rows.map((row) => row.filter((_, j) => j !== c)) })

  const cell =
    'w-full min-w-[7rem] bg-transparent px-2.5 py-2 text-sm outline-none transition focus:bg-accent/10 focus:shadow-[inset_0_0_0_1.5px_rgb(var(--accent))]'

  return (
    <div>
      <div className="overflow-x-auto rounded-lg border border-line">
        <table className="w-full border-collapse text-left">
          <thead>
            <tr className="bg-line/40">
              {headings.map((h, c) => (
                <th key={c} className="group relative border-r border-line p-0 last:border-r-0">
                  <input aria-label={`Column ${c + 1} heading`} value={h} onChange={(e) => setHeading(c, e.target.value)} className={`${cell} font-semibold`} />
                  {headings.length > 1 && (
                    <button
                      type="button"
                      onClick={() => removeCol(c)}
                      className="absolute right-1 top-1/2 -translate-y-1/2 rounded p-1 text-mute opacity-0 transition hover:text-bad focus:opacity-100 group-hover:opacity-100"
                      aria-label={`Remove column ${h}`}
                    >
                      <Trash2 size={12} />
                    </button>
                  )}
                </th>
              ))}
              <th className="w-10 p-0" />
            </tr>
          </thead>
          <tbody>
            {rows.map((row, r) => (
              <tr key={r} className="border-t border-line">
                {row.map((v, c) => (
                  <td key={c} className="border-r border-line p-0 last:border-r-0">
                    <input aria-label={`Row ${r + 1}, ${headings[c]}`} value={v} onChange={(e) => setCell(r, c, e.target.value)} className={`${cell} font-mono`} />
                  </td>
                ))}
                <td className="p-0 text-center">
                  <button type="button" onClick={() => removeRow(r)} className="p-2 text-mute transition hover:text-bad" aria-label={`Remove row ${r + 1}`}>
                    <Trash2 size={14} />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <div className="mt-3 flex gap-2">
        <button type="button" onClick={addRow} className="btn-ghost">
          <Plus size={14} /> Row
        </button>
        <button type="button" onClick={addCol} className="btn-ghost">
          <Plus size={14} /> Column
        </button>
      </div>
    </div>
  )
}
