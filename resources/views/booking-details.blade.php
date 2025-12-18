<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - NLA{{ $booking->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #1f2937;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .header {
            background: linear-gradient(135deg, #fcd513 0%, #f59e0b 100%);
            padding: 40px 30px;
            text-align: center;
            color: #1f2937;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .booking-id {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-checked-out {
            background: #e0e7ff;
            color: #3730a3;
        }

        .content {
            padding: 30px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #fcd513;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .room-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .total-section {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 20px;
            font-weight: 700;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.4);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 2px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
            color: #1f2937;
        }

        .modal-text {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        @media (max-width: 640px) {
            .header h1 {
                font-size: 24px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .total-section {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                ✗ {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="header">
                <h1>Booking Confirmation</h1>
                <div class="booking-id">Booking ID: NLA{{ $booking->id }}</div>
                <div class="status-badge status-{{ $booking->status }}">
                    {{ ucfirst($booking->status) }}
                </div>
            </div>

            <div class="content">
                <!-- Guest Information -->
                <div class="section">
                    <div class="section-title">Guest Information</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Name</div>
                            <div class="info-value">{{ $booking->guest_name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $booking->guest_email }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value">{{ $booking->guest_phone }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Booking Date</div>
                            <div class="info-value">{{ $booking->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Room Details -->
                <div class="section">
                    <div class="section-title">Room Details</div>
                    <!-- 
                    @if($booking->roomType && $booking->roomType->images && $booking->roomType->images->count() > 0)
                        <img src="{{ asset('storage/' . $booking->roomType->images->first()->image_path) }}"
                            alt="{{ $booking->roomType->name }}" class="room-image">
                    @endif -->

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Room Type</div>
                            <div class="info-value">{{ optional($booking->roomType)->name ?? 'N/A' }}</div>
                        </div>
                        <!-- <div class="info-item">
                            <div class="info-label">Room Number</div>
                            <div class="info-value">
                                {{ optional($booking->room)->room_number ? 'Room ' . $booking->room->room_number : 'Assigned at check-in' }}
                            </div>
                        </div> -->
                    </div>
                </div>

                <!-- Stay Details -->
                <div class="section">
                    <div class="section-title">Stay Details</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Check-in</div>
                            <div class="info-value">
                                @php
                                    $checkIn = $booking->check_in_date instanceof \Carbon\Carbon
                                        ? $booking->check_in_date
                                        : \Carbon\Carbon::parse($booking->check_in_date);
                                @endphp
                                {{ $checkIn->format('d M Y') }}
                                <br><small style="font-size: 14px; color: #6b7280;">12:00 PM</small>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Check-out</div>
                            <div class="info-value">
                                @php
                                    $checkOut = $booking->check_out_date instanceof \Carbon\Carbon
                                        ? $booking->check_out_date
                                        : \Carbon\Carbon::parse($booking->check_out_date);
                                @endphp
                                {{ $checkOut->format('d M Y') }}
                                <br><small style="font-size: 14px; color: #6b7280;">12:00 PM</small>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Number of Nights</div>
                            <div class="info-value">{{ $nights }} {{ $nights == 1 ? 'Night' : 'Nights' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="section">
                    <div class="total-section">
                        <span>Total Amount</span>
                        <span>NGN {{ number_format((float) $booking->amount, 0) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                @if(in_array($booking->status, ['pending', 'confirmed']))
                    <div class="actions">
                        <button onclick="showCancelModal()" class="btn btn-danger">
                            Cancel Booking
                        </button>
                        <a href="mailto:{{ config('mail.from.address', 'info@niconluxury.com') }}"
                            class="btn btn-secondary">
                            Contact Support
                        </a>
                    </div>
                @elseif($booking->status === 'cancelled')
                    <div class="alert alert-error">
                        This booking has been cancelled.
                    </div>
                @endif
            </div>
        </div>

        <div style="text-align: center; color: white; opacity: 0.8; margin-top: 20px;">
            <p>&copy; {{ date('Y') }} NICON Luxury Hotel. All rights reserved.</p>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">Cancel Booking?</div>
            <div class="modal-text">
                Are you sure you want to cancel this booking? This action cannot be undone.
                Your room will be released and made available for other guests.
            </div>
            <form method="POST" action="{{ route('booking.cancel', $booking->id) }}">
                @csrf
                <div class="modal-actions">
                    <button type="button" onclick="hideCancelModal()" class="btn btn-secondary">
                        No, Keep Booking
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Yes, Cancel Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCancelModal() {
            document.getElementById('cancelModal').classList.add('active');
        }

        function hideCancelModal() {
            document.getElementById('cancelModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('cancelModal').addEventListener('click', function (e) {
            if (e.target === this) {
                hideCancelModal();
            }
        });
    </script>
</body>

</html>