@if ($paginator->hasPages())
    <nav style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem">
        @if ($paginator->onFirstPage())
            <span style="padding:0.3rem 0.75rem;border:1px solid var(--border-dim);border-radius:4px;color:var(--text-muted);opacity:0.5">← Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               style="padding:0.3rem 0.75rem;border:1px solid var(--border-dim);border-radius:4px;color:var(--gold);text-decoration:none">← Prev</a>
        @endif

        <span style="color:var(--text-muted)">Page {{ $paginator->currentPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               style="padding:0.3rem 0.75rem;border:1px solid var(--border-dim);border-radius:4px;color:var(--gold);text-decoration:none">Next →</a>
        @else
            <span style="padding:0.3rem 0.75rem;border:1px solid var(--border-dim);border-radius:4px;color:var(--text-muted);opacity:0.5">Next →</span>
        @endif
    </nav>
@endif
