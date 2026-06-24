@extends('events::admin.console.layout', ['currentTab' => 'overview'])

@section('console-content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Console Overview & Chart --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Console Overview Info Box --}}
            <div class="glass-panel rounded-2xl p-6 space-y-4 text-text-primary">
                <h3 class="text-base font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-lg">info</span>
                    Console Overview
                </h3>
                <p class="text-sm text-text-secondary leading-relaxed">
                    Welcome to your Event Console workspace. From this single interface, you can edit general settings, set custom questions, configure approval response templates, and track real-time attendee registrations.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    {{-- Permalink Shortcut --}}
                    <div class="bg-dark-surface-lighter/50 border border-dark-border p-4 rounded-xl space-y-2">
                        <span class="text-xs text-text-secondary block">Shortcut Permalink Url</span>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs text-[#2563EB] font-mono truncate">{{ url('/event/' . $event->slug) }}</span>
                            <button onclick="navigator.clipboard.writeText('{{ url('/event/' . $event->slug) }}'); alert('Link copied to clipboard!')" class="text-text-secondary hover:text-text-primary text-xs flex items-center">
                                <span class="material-symbols-outlined text-sm">content_copy</span>
                            </button>
                        </div>
                    </div>
                    {{-- Live Guest scan / directory --}}
                    <div class="bg-dark-surface-lighter/50 border border-dark-border p-4 rounded-xl space-y-2">
                        <span class="text-xs text-text-secondary block">Live Guest Directory Link</span>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs text-amber-400 font-mono truncate">{{ route('admin.events.console.attendees', $event) }}</span>
                            <a href="{{ route('admin.events.console.attendees', $event) }}" wire:navigate class="px-2 py-1 rounded bg-amber-500/20 text-amber-400 text-[10px] font-bold border border-amber-500/30 hover:bg-amber-500/30">
                                OPEN DIRECTORY
                            </a>
                        </div>
                    </div>
                    {{-- Feedback Form Link --}}
                    <div class="bg-dark-surface-lighter/50 border border-dark-border p-4 rounded-xl space-y-2">
                        <span class="text-xs text-text-secondary block">Attendee Feedback Form Link</span>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs text-emerald-400 font-mono truncate">{{ url('/event/' . $event->slug . '/feedback') }}</span>
                            <button onclick="navigator.clipboard.writeText('{{ url('/event/' . $event->slug . '/feedback') }}'); alert('Feedback link copied!')" class="text-text-secondary hover:text-text-primary text-xs flex items-center">
                                <span class="material-symbols-outlined text-sm">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Registration Trend Card (Chart.js) --}}
            <div class="glass-panel rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-400 text-lg">bar_chart</span>
                        Registration Trend
                    </h3>
                    <select id="chartPeriodSelect" class="bg-dark-surface border border-dark-border text-xs rounded-lg px-2.5 py-1 text-text-primary focus:outline-none">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                    </select>
                </div>
                
                {{-- Canvas for Chart.js --}}
                <div class="w-full h-48 bg-dark-surface/50 border border-dark-border rounded-xl p-4 relative">
                    <canvas id="registrationTrendChart"></canvas>
                </div>
            </div>

        </div>

        {{-- Right Column: Realtime Event Feed --}}
        <div class="space-y-6">
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-400 text-lg">history</span>
                    Realtime Event Feed
                </h3>
                
                @php
                    $recentRegistrations = $event->registrations()->latest()->take(6)->get();
                @endphp

                <div class="space-y-4 max-h-[350px] overflow-y-auto pr-1">
                    @if($recentRegistrations->isEmpty())
                        <div class="flex flex-col items-center justify-center py-8 text-center text-text-secondary">
                            <span class="material-symbols-outlined text-3xl mb-1">inbox</span>
                            <p class="text-xs">No registrations yet</p>
                        </div>
                    @else
                        @foreach($recentRegistrations as $reg)
                            @if($reg->check_in)
                                {{-- Checked In Feed Item --}}
                                <div class="flex gap-3 text-xs">
                                    <div class="h-6 w-6 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-sm">check_circle</span>
                                    </div>
                                    <div>
                                        <p class="text-text-primary font-medium">{{ $reg->name }} checked in</p>
                                        <span class="text-[10px] text-text-secondary">{{ $reg->updated_at->diffForHumans() }} &bull; Main Entrance</span>
                                    </div>
                                </div>
                            @elseif($reg->status === 'confirmed')
                                {{-- Approved/Confirmed Feed Item --}}
                                <div class="flex gap-3 text-xs">
                                    <div class="h-6 w-6 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-sm">mail</span>
                                    </div>
                                    <div>
                                        <p class="text-text-primary font-medium">Ticket confirmed for {{ $reg->name }}</p>
                                        <span class="text-[10px] text-text-secondary">{{ $reg->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @else
                                {{-- Pending Feed Item --}}
                                <div class="flex gap-3 text-xs">
                                    <div class="h-6 w-6 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-sm">hourglass_empty</span>
                                    </div>
                                    <div>
                                        <p class="text-text-primary font-medium">{{ $reg->name }} requested registration</p>
                                        <span class="text-[10px] text-text-secondary">{{ $reg->created_at->diffForHumans() }} &bull; Awaiting approval</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <hr class="border-dark-border"/>
                <a href="{{ route('admin.events.console.attendees', $event) }}" wire:navigate class="block w-full py-2 text-center text-xs font-bold text-[#2563EB] hover:underline">
                    View All Attendees
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        let trendChartInstance = null;

        function initRegistrationTrendChart() {
            const canvas = document.getElementById('registrationTrendChart');
            if (!canvas) return;

            if (typeof Chart === 'undefined') {
                setTimeout(initRegistrationTrendChart, 100);
                return;
            }

            // Clean up existing instance if any
            if (trendChartInstance) {
                trendChartInstance.destroy();
                trendChartInstance = null;
            }

            const rawData = @json($trendData ?? []);
            const select = document.getElementById('chartPeriodSelect');
            if (!select) return;

            // Function to retrieve dynamic theme colors from CSS variables
            function getThemeColors() {
                const themeEl = document.querySelector('.console-theme') || document.documentElement;
                const computed = getComputedStyle(themeEl);
                const isDark = document.documentElement.classList.contains('dark');

                return {
                    textSecondary: computed.getPropertyValue('--c-text-secondary').trim() || '#9CA3AF',
                    border: computed.getPropertyValue('--c-border').trim() || (isDark ? '#27272A' : '#E5E7EB'),
                };
            }

            const ctx = canvas.getContext('2d');
            
            // Generate primary gradient
            let gradient = ctx.createLinearGradient(0, 0, 0, 160);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

            // Function to render chart based on current selection
            function renderChart() {
                const days = parseInt(select.value) || 30;
                const slicedData = rawData.slice(-days);
                
                const labels = slicedData.map(d => d.label);
                const values = slicedData.map(d => d.count);
                const colors = getThemeColors();

                if (trendChartInstance) {
                    trendChartInstance.data.labels = labels;
                    trendChartInstance.data.datasets[0].data = values;
                    trendChartInstance.options.scales.x.grid.color = colors.border;
                    trendChartInstance.options.scales.x.ticks.color = colors.textSecondary;
                    trendChartInstance.options.scales.y.grid.color = colors.border;
                    trendChartInstance.options.scales.y.ticks.color = colors.textSecondary;
                    trendChartInstance.update();
                    return;
                }

                trendChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Registrations',
                            data: values,
                            borderColor: '#2563EB',
                            borderWidth: 2.5,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#2563EB',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(11, 11, 11, 0.9)',
                                titleColor: '#FCFCFC',
                                bodyColor: '#FCFCFC',
                                borderWidth: 1,
                                borderColor: colors.border,
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    title: function(context) {
                                        const idx = context[0].dataIndex;
                                        const item = slicedData[idx];
                                        return item.date;
                                    },
                                    label: function(context) {
                                        return `Registrations: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: colors.border,
                                    drawOnChartArea: false,
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: colors.textSecondary,
                                    font: {
                                        size: 10,
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    color: colors.border,
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: colors.textSecondary,
                                    stepSize: 1,
                                    precision: 0,
                                    font: {
                                        size: 10,
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    }
                                },
                                min: 0
                            }
                        }
                    }
                });
            }

            // Init rendering
            renderChart();

            // Listen to dropdown changes
            select.addEventListener('change', renderChart);

            // Listen to theme switcher via MutationObserver on HTML element
            const observer = new MutationObserver(() => {
                const colors = getThemeColors();
                if (trendChartInstance) {
                    trendChartInstance.options.scales.x.grid.color = colors.border;
                    trendChartInstance.options.scales.x.ticks.color = colors.textSecondary;
                    trendChartInstance.options.scales.y.grid.color = colors.border;
                    trendChartInstance.options.scales.y.ticks.color = colors.textSecondary;
                    if (trendChartInstance.options.plugins.tooltip) {
                        trendChartInstance.options.plugins.tooltip.borderColor = colors.border;
                    }
                    trendChartInstance.update();
                }
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            // Store observer on canvas to clean it up if needed
            canvas._themeObserver = observer;
        }

        // Initialize on DOM load and Livewire navigation
        document.addEventListener('DOMContentLoaded', initRegistrationTrendChart);
        document.addEventListener('livewire:navigated', initRegistrationTrendChart);
        
        // Also call immediately in case script is injected after events
        setTimeout(initRegistrationTrendChart, 50);
    })();
</script>
@endpush
