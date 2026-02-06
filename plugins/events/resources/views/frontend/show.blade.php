<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }} - iCCom Events</title>
    <meta name="description" content="{{ $event->meta_description ?: $event->description }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .event-info-card {
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .registration-card {
            position: sticky;
            top: 20px;
        }
        .gallery-image {
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
        }
        .event-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Event Header -->
    <div class="event-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    @if($event->category)
                    <span class="badge bg-warning text-dark mb-3">{{ $event->category->name }}</span>
                    @endif
                    <h1 class="display-4 mb-3">{{ $event->title }}</h1>
                    <p class="lead">{{ $event->description }}</p>
                    
                    <div class="d-flex flex-wrap gap-4 mt-4">
                        <div>
                            <i class="material-icons align-middle">event</i>
                            <strong>{{ $event->formatted_date_range }}</strong>
                        </div>
                        <div>
                            <i class="material-icons align-middle">location_on</i>
                            {{ $event->location ?: 'Online Event' }}
                        </div>
                        <div>
                            <i class="material-icons align-middle">{{ $event->event_type == 'online' ? 'videocam' : 'place' }}</i>
                            {{ ucfirst($event->event_type) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Featured Image -->
                @if($event->featuredImage)
                <img src="{{ $event->featuredImage->url }}" alt="{{ $event->title }}" class="img-fluid rounded mb-4">
                @endif

                <!-- Event Content -->
                <div class="event-content mb-5">
                    {!! $event->content !!}
                </div>

                <!-- Event Gallery -->
                @if($event->gallery_images && count($event->gallery_images) > 0)
                <div class="mb-5">
                    <h4 class="mb-3">Event Gallery</h4>
                    <div class="row g-3">
                        @foreach($event->gallery_images as $imageId)
                            @php
                                $image = \App\Models\Media::find($imageId);
                            @endphp
                            @if($image)
                            <div class="col-md-4">
                                <img src="{{ $image->url }}" alt="Gallery" class="gallery-image w-100">
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Location Map -->
                @if($event->location_url)
                <div class="mb-5">
                    <h4 class="mb-3">Location</h4>
                    <div class="card event-info-card">
                        <div class="card-body">
                            <h6><i class="material-icons align-middle">location_on</i> {{ $event->location }}</h6>
                            @if($event->location_address)
                            <p class="text-muted mb-3">{{ $event->location_address }}</p>
                            @endif
                            <a href="{{ $event->location_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="material-icons align-middle">map</i> View on Google Maps
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="registration-card">
                    <!-- Registration Card -->
                    @if($event->requires_registration)
                    <div class="card event-info-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Event Registration</h5>
                            
                            @if($event->is_registration_open)
                                <div class="alert alert-success">
                                    <i class="material-icons align-middle">check_circle</i>
                                    <strong>Registration Open!</strong>
                                </div>
                                
                                @if($event->max_participants)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Participants</small>
                                        <small>{{ $event->registered_count }}/{{ $event->max_participants }}</small>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ ($event->registered_count / $event->max_participants) * 100 }}%">
                                        </div>
                                    </div>
                                    @if($event->available_slots)
                                    <small class="text-muted">{{ $event->available_slots }} slots remaining</small>
                                    @endif
                                </div>
                                @endif

                                @if($event->registration_deadline)
                                <p class="small text-muted mb-3">
                                    <i class="material-icons align-middle" style="font-size: 16px;">schedule</i>
                                    Registration closes: {{ $event->registration_deadline->format('M d, Y H:i') }}
                                </p>
                                @endif

                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#registrationModal">
                                    <i class="material-icons align-middle">person_add</i> Register Now
                                </button>
                            @else
                                <div class="alert alert-secondary">
                                    <i class="material-icons align-middle">block</i>
                                    <strong>Registration Closed</strong>
                                </div>
                                
                                @if($event->max_participants && $event->registered_count >= $event->max_participants)
                                <p class="small text-muted">Event is fully booked</p>
                                @elseif($event->registration_deadline && $event->registration_deadline->isPast())
                                <p class="small text-muted">Registration deadline has passed</p>
                                @endif
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Event Details Card -->
                    <div class="card event-info-card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Event Details</h6>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">Event Type</small>
                                <strong>{{ ucfirst($event->event_type) }}</strong>
                            </div>

                            @if($event->event_type == 'online' && $event->online_meeting_url)
                            <div class="mb-3">
                                <small class="text-muted d-block">Meeting Link</small>
                                <a href="{{ $event->online_meeting_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="material-icons align-middle">videocam</i> Join Online
                                </a>
                            </div>
                            @endif

                            @if($event->author)
                            <div class="mb-3">
                                <small class="text-muted d-block">Organizer</small>
                                <strong>{{ $event->author->name }}</strong>
                            </div>
                            @endif

                            <div class="mb-3">
                                <small class="text-muted d-block">Status</small>
                                @if($event->is_upcoming)
                                <span class="badge bg-info">Upcoming</span>
                                @elseif($event->is_ongoing)
                                <span class="badge bg-success">Ongoing</span>
                                @else
                                <span class="badge bg-secondary">Completed</span>
                                @endif
                            </div>

                            <hr>

                            <div class="d-grid gap-2">
                                <a href="{{ route('events.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="material-icons align-middle">arrow_back</i> Back to Events
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Modal -->
    @if($event->requires_registration && $event->is_registration_open)
    <div class="modal fade" id="registrationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register for {{ $event->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="registrationForm" action="{{ route('events.register', $event->slug) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Organization</label>
                            <input type="text" class="form-control" name="organization">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Registration</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
