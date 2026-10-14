import { FileSpreadsheet } from 'lucide-react'
import FormatReportPage from './FormatReportPage'

export default function ExcelReportPage() {
  return (
    <FormatReportPage
      kind="excel"
      title="Excel report"
      description="Edit the data, then download a styled .xlsx with a dark header row and frozen headings."
      icon={<FileSpreadsheet size={22} />}
      loadFrom={[
        { id: 'excel-inventory', label: 'Inventory' },
        { id: 'excel-attendance', label: 'Attendance' },
        { id: 'csv-employees', label: 'Employees' },
      ]}
    />
  )
}
