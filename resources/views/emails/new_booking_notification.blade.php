

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New booking received</title>
    <style>
        body { margin:0; padding:0; background:#f5f7fa; color:#1f2937; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial; }
        .container { width:680px; max-width:100%; margin:24px auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; }
        .header { text-align:center; padding:24px 24px 8px; }
        .brand { font-size:20px; font-weight:600; color:#000000; }
        .section { padding:8px 24px 24px; }
        .row { font-size:0; }
        .col { display:inline-block; width:calc(50% - 8px); min-width:260px; vertical-align:top; font-size:14px; }
        @media only screen and (max-width: 600px) {
            .container { width:100% !important; border-radius:0 !important; }
            .row { display:block !important; }
            .col { width:100% !important; display:block !important; }
            .card { margin-bottom:12px !important; }
            .header { padding:16px !important; }
        }
        .card { border:1px solid #e5e7eb; border-radius:8px; padding:14px; margin-bottom:16px; }
        .card-title { display:inline-block; background:#e6fffa; color:#132ef4; font-weight:700; font-size:12px; padding:6px 10px; border-radius:4px; text-transform:uppercase; letter-spacing:.02em; }
        .list { margin:10px 0 0; padding:0; list-style:none; }
        .list li { margin:6px 0; }
        .total-wrap { border-top:1px solid #e5e7eb; margin-top:12px; padding-top:12px; }
        .total { border:1px solid #e5e7eb; border-radius:6px; padding:12px 16px; display:flex; justify-content:space-between; font-weight:600; background:#f9fafb; }
        .cta { text-align:center; padding:8px 24px 24px; }
        .button { display:inline-block; background:#fcd513; color:#fff; text-decoration:none; padding:10px 18px; border-radius:6px; font-weight:600; }
        .footer { text-align:center; color:#6b7280; font-size:12px; padding:12px 24px 18px; }
        a { color:#fa0b17; text-decoration:none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{-- <div class="brand">{{ config('mail.from.name', config('app.name', 'Hotel')) }}</div> --}}
                  <div class="brand">New Booking Details</div>
        </div>

        <div class="section">
            <div class="row">
                <div class="col">
                    <div class="card">
                        <span class="card-title">Customer Booking Details</span>
                        <ul class="list">
                            @if(isset($all_bookings) && count($all_bookings) > 1)
                                <li>Booking Numbers: 
                                    @foreach($all_bookings as $index => $b)
                                        NLA{{ $b->id }}{{ $index < count($all_bookings) - 1 ? ', ' : '' }}
                                    @endforeach
                                </li>
                                <li>Total Rooms Booked: {{ $number_of_rooms ?? count($all_bookings) }}</li>
                            @else
                                <li>Booking Number: NLA{{ $booking->id }}</li>
                            @endif
                            <li>Booking Status: {{ ucfirst($booking->status) }}</li>
                            <li>Booking Date: {{ optional($booking->created_at)->format('d/m/Y H:i') }}</li>
                        </ul>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <span class="card-title">Customer Personal Details</span>
                        <ul class="list">
                            <li>Name: {{ $booking->guest_name }}</li>
                            <li>e‑Mail: <a href="mailto:{{ $booking->guest_email }}">{{ $booking->guest_email }}</a></li>
                            @if(!empty($booking->guest_phone))
                            <li>Phone: {{ $booking->guest_phone }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col">
                    <div class="card">
                        <span class="card-title">Rooms Booked</span>
                        <ul class="list">
                            <li>{{ optional($booking->roomType)->name ?? 'Room' }}</li>
                            <li>Check‑in Date: {{ $booking->check_in_date instanceof \Carbon\Carbon ? $booking->check_in_date->format('d/m/Y H:i') : $booking->check_in_date }}</li>
                            <li>Check‑out Date: {{ $booking->check_out_date instanceof \Carbon\Carbon ? $booking->check_out_date->format('d/m/Y H:i') : $booking->check_out_date }}</li>
                            
                            @if(isset($assigned_rooms) && is_array($assigned_rooms) && count($assigned_rooms) > 0)
                                <li><strong>Assigned Rooms:</strong></li>
                                @foreach($assigned_rooms as $room)
                                    <li style="margin-left: 15px;">• Room {{ $room['room_number'] }} ({{ $room['status'] }})</li>
                                @endforeach
                            @else
                                <li>{{ optional($booking->roomType)->name }} — {{ optional($booking->room)->room_number ? 'Room ' . $booking->room->room_number : 'Assigned at check‑in' }}</li>
                            @endif
                            
                            <li style="margin-top: 10px;"><strong>Total Amount: NGN {{ number_format((float) ($total_amount ?? $booking->amount), 0) }}</strong></li>
                        </ul>
                    </div>
                </div>
              
            </div>




        </div>

        
    </div>
</body>
</html>