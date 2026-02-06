<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $form->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 40px 0;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .form-title {
            color: #333;
            margin-bottom: 10px;
        }
        .form-description {
            color: #666;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .required-mark {
            color: #dc3545;
        }
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 4px;
        }
        .btn-submit {
            background-color: #0d6efd;
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 5px;
        }
        .btn-submit:hover {
            background-color: #0b5ed7;
        }
        .alert {
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <h1 class="form-title">{{ $form->name }}</h1>
            
            @if($form->description)
            <p class="form-description">{{ $form->description }}</p>
            @endif

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {!! $form->renderForm(['class' => 'needs-validation', 'novalidate' => true]) !!}
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">
                Powered by iCCom CMS
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
