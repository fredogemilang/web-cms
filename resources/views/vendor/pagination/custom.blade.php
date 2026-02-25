@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-rounded gap-2 mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link border-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #f8f9fa; color: #ced4da;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link border-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="width: 40px; height: 40px; color: #333;" aria-label="@lang('pagination.previous')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link border-0 rounded-circle d-flex align-items-center justify-content-center bg-transparent" style="width: 40px; height: 40px;">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link border-0 rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; background-color: var(--primary-blue, #29abe2); color: white;">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link border-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" href="{{ $url }}" style="width: 40px; height: 40px; color: #333; background-color: white;">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link border-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" href="{{ $paginator->nextPageUrl() }}" rel="next" style="width: 40px; height: 40px; color: #333;" aria-label="@lang('pagination.next')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link border-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #f8f9fa; color: #ced4da;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
    <style>
        .pagination-rounded .page-link:focus {
            box-shadow: none;
        }
        .pagination-rounded .page-link:hover {
            background-color: var(--cta-orange, #f68d2e) !important;
            color: white !important;
        }
    </style>
@endif
