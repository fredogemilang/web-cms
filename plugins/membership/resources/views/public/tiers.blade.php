<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Tiers - iCCom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .tier-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .tier-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .tier-header {
            padding: 30px;
            color: white;
            text-align: center;
        }
        .tier-price {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 20px 0;
        }
        .tier-duration {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .benefit-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .benefit-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="text-center mb-5">
            <h1 class="display-4 mb-3">Membership Tiers</h1>
            <p class="lead text-muted">Choose the membership that fits your needs</p>
        </div>

        <div class="row g-4">
            @foreach($tiers as $tier)
            <div class="col-md-6 col-lg-3">
                <div class="card tier-card">
                    <div class="tier-header" style="background: linear-gradient(135deg, {{ $tier->color }} 0%, {{ $tier->color }}dd 100%);">
                        <i class="material-icons" style="font-size: 48px;">{{ $tier->icon }}</i>
                        <h3 class="mt-3">{{ $tier->name }}</h3>
                        <div class="tier-price">{{ $tier->formatted_price }}</div>
                        <div class="tier-duration">{{ $tier->duration_text }}</div>
                    </div>
                    
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">{{ $tier->description }}</p>
                        
                        <h6 class="mb-3">Benefits:</h6>
                        <div class="benefits-list">
                            @if($tier->benefits)
                                @foreach($tier->benefits as $benefit)
                                <div class="benefit-item">
                                    <i class="material-icons text-success align-middle" style="font-size: 20px;">check_circle</i>
                                    <span class="ms-2">{{ $benefit }}</span>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        
                        <div class="mt-4 d-grid">
                            <a href="{{ route('membership.register') }}?tier={{ $tier->slug }}" class="btn btn-primary">
                                Choose Plan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">Have questions? <a href="#">Contact us</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
