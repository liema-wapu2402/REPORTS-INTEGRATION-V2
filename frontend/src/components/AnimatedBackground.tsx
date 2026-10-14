/** Fixed, non-interactive backdrop: drifting grid, glow orbs and a slow scan line. */
export default function AnimatedBackground() {
  return (
    <div aria-hidden className="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
      <div className="absolute inset-0 animate-gridmove bg-grid" />
      <div className="absolute -left-32 top-10 h-[420px] w-[420px] animate-float rounded-full bg-accent/20 blur-[110px]" />
      <div
        className="absolute -right-24 top-1/3 h-[380px] w-[380px] animate-float rounded-full bg-accent2/20 blur-[110px]"
        style={{ animationDelay: '-9s' }}
      />
      <div className="absolute inset-x-0 top-0 h-px animate-scan bg-gradient-to-r from-transparent via-accent/60 to-transparent" />
    </div>
  )
}
