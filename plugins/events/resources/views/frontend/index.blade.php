<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - iCCom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .event-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .event-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .event-category {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .event-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(255,255,255,0.95);
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .upcoming-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <!-- Upcoming Event Highlight -->
        @php
            $upcomingEvent = \Plugins\Events\Models\Event::published()->upcoming()->first();
        @endphp
        
        @if($upcomingEvent)
        <div class="upcoming-highlight">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-warning text-dark mb-2">Next Event</span>
                    <h2 class="mb-3">{{ $upcomingEvent->title }}</h2>
                    <p class="mb-3">{{ Str::limit($upcomingEvent->description, 150) }}</p>
                    <div class="d-flex gap-3 mb-3">
                        <div>
                            <i class="material-icons align-middle">event</i>
                            {{ $upcomingEvent->formatted_date_range }}
                        </div>
                        <div>
                            <i class="material-icons align-middle">location_on</i>
                            {{ $upcomingEvent->location ?: 'Online' }}
                        </div>
                    </div>
                    <a href="{{ route('events.show', $upcomingEvent->slug) }}" class="btn btn-light">
                        View Details <i class="material-icons align-middle">arrow_forward</i>
                    </a>
                </div>
                @if($upcomingEvent->featuredImage)
                <div class="col-md-4">
                    <img src="{{ $upcomingEvent->featuredImage->url }}" alt="{{ $upcomingEvent->title }}" class="img-fluid rounded">
                </div>
                @endif
            </div>
        </div>
        @endif

        <h1 class="mb-4">All Events</h1>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" action="{{ route('events.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach(\Plugins\Events\Models\EventCategory::orderBy('order')->get() as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>Offline</option>
                            <option value="hybrid" {{ request('type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="time" class="form-select" onchange="this.form.submit()">
                            <option value="">All Time</option>
                            <option value="upcoming" {{ request('time') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ request('time') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="past" {{ request('time') == 'past' ? 'selected' : '' }}>Past</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="material-icons align-middle">filter_list</i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Events Grid -->
        <div class="row g-4">
            @forelse($events as $event)
            <div class="col-md-4">
                <div class="card event-card">
                    <div class="position-relative">
                        @if($event->featuredImage)
                        <img src="{{ $event->featuredImage->url }}" class="event-image" alt="{{ $event->title }}">
                        @else
                        <div class="event-image bg-secondary d-flex align-items-center justify-content-center">
                            <i class="material-icons" style="font-size: 64px; color: #fff;">event</i>
                        </div>
                        @endif
                        
                        <span class="event-type-badge">
                            {{ ucfirst($event->event_type) }}
                        </span>
                    </div>
                    
                    <div class="card-body">
                        @if($event->category)
                        <span class="event-category mb-2" style="background-color: {{ $event->category->color }}20; color: {{ $event->category->color }};">
                            {{ $event->category->name }}
                        </span>
                        @endif
                        
                        <h5 class="card-title mt-2">{{ $event->title }}</h5>
                        <p class="card-text text-muted small">{{ Str::limit($event->description, 100) }}</p>
                        
                        <div class="d-flex align-items-center text-muted small mb-2">
                            <i class="material-icons me-1" style="font-size: 18px;">event</i>
                            {{ $event->start_date->format('M d, Y') }}
                        </div>
                        
                        <div class="d-flex align-items-center text-muted small mb-3">
                            <i class="material-icons me-1" style="font-size: 18px;">location_on</i>
                            {{ $event->location ?: 'Online' }}
                        </div>
                        
                        @if($event->requires_registration)
                        <div class="mb-3">
                            @if($event->is_registration_open)
                            <span class="badge bg-success">Registration Open</span>
                            @if($event->available_slots)
                            <small class="text-muted ms-2">{{ $event->available_slots }} slots left</small>
                            @endif
                            @else
                            <span class="badge bg-secondary">Registration Closed</span>
                            @endif
                        </div>
                        @endif
                        
                        <a href="{{ route('events.show', $event->slug) }}" class="btn btn-primary btn-sm w-100">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="material-icons" style="font-size: 64px; color: #ccc;">event_busy</i>
                    <h5 class="mt-3 text-muted">No events found</h5>
                    <p class="text-muted">Check back later for upcoming events</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($events->hasPages())
        <div class="mt-4">
            {{ $events->links() }}
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
