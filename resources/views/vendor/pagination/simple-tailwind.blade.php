@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-2">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center p-2 text-gray-400 bg-white border border-gray-300 cursor-not-allowed leading-5 rounded-md dark:text-gray-500 dark:bg-gray-800 dark:border-gray-600" aria-disabled="true" aria-label="Sebelumnya">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center p-2 text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:bg-dinas-green/10 hover:text-dinas-green focus:outline-none focus:ring ring-gray-300 active:bg-gray-200 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" aria-label="Sebelumnya">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center p-2 text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:bg-dinas-green/10 hover:text-dinas-green focus:outline-none focus:ring ring-gray-300 active:bg-gray-200 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" aria-label="Berikutnya">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <span class="inline-flex items-center justify-center p-2 text-gray-400 bg-white border border-gray-300 cursor-not-allowed leading-5 rounded-md dark:text-gray-500 dark:bg-gray-800 dark:border-gray-600" aria-disabled="true" aria-label="Berikutnya">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        @endif

    </nav>
@endif
