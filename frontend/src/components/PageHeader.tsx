import type { ReactNode } from 'react'

interface Props {
  title: string
  description?: string
  icon?: ReactNode
  actions?: ReactNode
}

export default function PageHeader({ title, description, icon, actions }: Props) {
  return (
    <div className="flex flex-wrap items-start justify-between gap-4 animate-rise">
      <div className="flex items-start gap-3.5">
        {icon && (
          <div className="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-accent/30 bg-accent/10 text-accent">
            {icon}
          </div>
        )}
        <div>
          <h1 className="text-2xl font-semibold sm:text-3xl">{title}</h1>
          {description && <p className="mt-1 max-w-2xl text-sm text-mute sm:text-base">{description}</p>}
        </div>
      </div>
      {actions}
    </div>
  )
}
