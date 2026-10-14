import { FileType2 } from 'lucide-react'
import FormatReportPage from './FormatReportPage'

export default function DocxReportPage() {
  return (
    <FormatReportPage
      kind="docx"
      title="Word report"
      description="Write an introduction, add a data table, and download an editable .docx."
      icon={<FileType2 size={22} />}
      withNarrative
      loadFrom={[
        { id: 'docx-service-report', label: 'Sales summary' },
        { id: 'csv-employees', label: 'Employees' },
        { id: 'excel-attendance', label: 'Attendance' },
      ]}
    />
  )
}
