import { Table2 } from 'lucide-react'
import FormatReportPage from './FormatReportPage'

export default function CsvReportPage() {
  return (
    <FormatReportPage
      kind="csv"
      title="CSV report"
      description="Plain UTF-8 CSV that opens cleanly in Excel. The lightest option for very large row counts."
      icon={<Table2 size={22} />}
      loadFrom={[
        { id: 'csv-transactions', label: 'Transactions' },
        { id: 'csv-employees', label: 'Employees' },
        { id: 'excel-inventory', label: 'Inventory' },
      ]}
    />
  )
}
