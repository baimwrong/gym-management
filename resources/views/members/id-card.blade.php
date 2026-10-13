<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member ID Pass - {{ $member->full_name }}</title>

    <!-- Local Offline Assets -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <style>
        body {
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .pass-card {
            width: 380px;
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            border: 1px solid #cbd5e1;
        }

        .pass-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }

        .pass-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            font-size: 2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -40px auto 1rem;
            border: 4px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .pass-card {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary fw-bold shadow-sm">
            <i class="fa-solid fa-print me-1"></i> Print / Save PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary">
            Close
        </button>
    </div>

    <!-- Official Gym ID Badge Card -->
    <div class="pass-card">
        <div class="pass-header">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                <i class="fa-solid fa-dumbbell text-info fs-4"></i>
                <h5 class="fw-bold mb-0 tracking-wider">{{ config('gym.name') }}</h5>
            </div>
            <div class="small text-light text-opacity-75 tracking-widest text-uppercase" style="font-size: 0.65rem;">
                Official Digital Member Pass
            </div>
        </div>

        <div class="px-4 pb-4 pt-0 text-center bg-white">
            <div class="pass-avatar">
                {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
            </div>

            <h4 class="fw-bold mb-0 text-slate-900">{{ $member->full_name }}</h4>
            <div class="badge bg-primary-subtle text-primary border border-primary-subtle my-2 px-3 py-1 fw-bold">
                {{ $member->activeSubscription?->plan->name ?? 'Standard Club Pass' }}
            </div>

            <!-- Offline QR Matrix -->
            <div class="my-3 d-inline-block p-2 bg-light rounded-3 border">
                {!! $qrCodeSvg !!}
            </div>

            <div class="mb-3">
                {!! $barcodeSvg !!}
            </div>

            <div class="row g-2 text-start p-3 bg-light rounded-3 border small">
                <div class="col-6">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">MEMBER ID</span>
                    <strong class="text-dark">{{ $member->member_code }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">VALID THRU</span>
                    <strong class="text-success">{{ $member->activeSubscription?->end_date?->format('M d, Y') ?? 'N/A' }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">BLOOD TYPE</span>
                    <strong class="text-danger">{{ $member->blood_type ?? 'N/A' }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">EMERGENCY CONTACT</span>
                    <strong class="text-dark text-truncate d-block">{{ $member->emergency_contact_phone ?? 'N/A' }}</strong>
                </div>
            </div>

            <div class="mt-3 text-muted text-center" style="font-size: 0.65rem;">
                {{ config('gym.address') }}, {{ config('gym.city') }} &bull; {{ config('gym.phone') }}
            </div>
        </div>
    </div>
</body>
</html>
