# Multiple Room Booking Fix

## Issue Found ✅

The problem was in the **validation rules**. The `number_of_rooms` parameter was not included in the `BookingStoreRequest` validation rules, so Laravel was **stripping it out** during the validation process.

### What Was Happening:
1. Frontend sends: `{ ..., "number_of_rooms": 2 }`
2. Laravel validation runs
3. `number_of_rooms` is **removed** because it's not in the rules
4. Controller receives: `{ ... }` (no number_of_rooms)
5. Code defaults to: `$requestedCount = (int) ($data['number_of_rooms'] ?? 1);` → **Always 1**
6. Only **1 booking** is created instead of 2

---

## Fix Applied ✅

### File: `app/Http/Requests/BookingStoreRequest.php`

**Added validation rule:**
```php
'number_of_rooms' => 'sometimes|integer|min:1|max:10',
```

**Full rules now:**
```php
public function rules(): array
{
    return [
        'guest_name' => 'required|string|max:255',
        'guest_email' => 'required|email',
        'guest_phone' => 'required|string|max:20',
        'room_type_id' => 'required|exists:room_types,id',
        'check_in_date' => 'required|date',
        'check_out_date' => 'required|date|after:check_in_date',
        'number_of_rooms' => 'sometimes|integer|min:1|max:10', // NEW
    ];
}
```

### Validation Rules Explained:
- `sometimes` - Field is optional (defaults to 1 if not provided)
- `integer` - Must be a whole number
- `min:1` - At least 1 room
- `max:10` - Maximum 10 rooms per booking

---

## Additional Improvements ✅

### Enhanced Logging

Added comprehensive logging to track the booking process:

```php
Log::info('Booking request received', [
    'requested_count' => $requestedCount,
    'room_type_id' => $data['room_type_id'],
    'guest_name' => $data['guest_name'],
]);

Log::info('Available rooms found', [
    'requested' => $requestedCount,
    'found' => $availableRooms->count(),
    'room_ids' => $availableRooms->pluck('id')->toArray(),
]);

Log::info('Booking created', [
    'booking_id' => $booking->id,
    'room_id' => $room->id,
    'room_number' => $room->room_number,
]);

Log::info('All bookings created', [
    'total_bookings' => count($createdBookings),
    'booking_ids' => collect($createdBookings)->pluck('id')->toArray(),
]);
```

This helps debug any future issues by showing:
- How many rooms were requested
- How many rooms were found
- Each booking creation
- Final count of bookings created

---

## How It Works Now ✅

### Example: Booking 2 Rooms

**Request:**
```json
POST /api/bookings
{
  "guest_name": "John Doe",
  "guest_email": "john@example.com",
  "guest_phone": "+1234567890",
  "room_type_id": 1,
  "check_in_date": "2025-12-20",
  "check_out_date": "2025-12-22",
  "number_of_rooms": 2
}
```

**Process:**
1. ✅ Validation accepts `number_of_rooms: 2`
2. ✅ System finds 2 available rooms (e.g., Room 101 and Room 102)
3. ✅ Creates **2 separate booking records**:
   - Booking #1: Room 101
   - Booking #2: Room 102
4. ✅ Both rooms marked as "Reserved"
5. ✅ Returns both bookings in response

**Response:**
```json
{
  "message": "Bookings created and rooms assigned.",
  "number_of_rooms": 2,
  "total_amount": 1000.00,
  "booking": { /* First booking */ },
  "bookings": [
    {
      "id": 101,
      "guest_name": "John Doe",
      "room_id": 5,
      "room": { "room_number": "101" },
      "status": "pending",
      "amount": 500.00
    },
    {
      "id": 102,
      "guest_name": "John Doe",
      "room_id": 6,
      "room": { "room_number": "102" },
      "status": "pending",
      "amount": 500.00
    }
  ],
  "assigned_rooms": [
    { "id": 5, "room_number": "101", "status": "Reserved" },
    { "id": 6, "room_number": "102", "status": "Reserved" }
  ]
}
```

**Database Result:**
```
bookings table:
+-----+------------+---------+--------------+---------+
| id  | guest_name | room_id | room_number  | status  |
+-----+------------+---------+--------------+---------+
| 101 | John Doe   | 5       | 101          | pending |
| 102 | John Doe   | 6       | 102          | pending |
+-----+------------+---------+--------------+---------+

rooms table:
+----+--------------+----------+
| id | room_number  | status   |
+----+--------------+----------+
| 5  | 101          | Reserved |
| 6  | 102          | Reserved |
+----+--------------+----------+
```

---

## Testing

### Manual Test:
1. Go to booking page
2. Select a room type
3. Set number of rooms to **2**
4. Fill in guest details
5. Submit booking
6. Check response - should show 2 bookings
7. Check database - should have 2 booking records
8. Check admin panel - should see 2 separate bookings

### API Test:
```bash
curl -X POST "http://localhost:8000/api/bookings" \
  -H "Content-Type: application/json" \
  -d '{
    "guest_name": "Test Guest",
    "guest_email": "test@example.com",
    "guest_phone": "+1234567890",
    "room_type_id": 1,
    "check_in_date": "2025-12-20",
    "check_out_date": "2025-12-22",
    "number_of_rooms": 2
  }'
```

### Check Logs:
```bash
tail -f storage/logs/laravel.log
```

Look for:
```
[timestamp] local.INFO: Booking request received {"requested_count":2,...}
[timestamp] local.INFO: Available rooms found {"requested":2,"found":2,...}
[timestamp] local.INFO: Booking created {"booking_id":101,...}
[timestamp] local.INFO: Booking created {"booking_id":102,...}
[timestamp] local.INFO: All bookings created {"total_bookings":2,...}
```

---

## Files Modified

1. **`app/Http/Requests/BookingStoreRequest.php`**
   - Added `number_of_rooms` validation rule

2. **`app/Http/Controllers/BookingController.php`**
   - Added comprehensive logging for debugging

3. **`docs/test_multiple_rooms.sh`**
   - Created test script for verification

---

## Summary

### Problem:
- ❌ Booking 2 rooms only created 1 booking

### Root Cause:
- ❌ `number_of_rooms` not in validation rules
- ❌ Laravel stripped it out during validation
- ❌ Controller always defaulted to 1 room

### Solution:
- ✅ Added `number_of_rooms` to validation rules
- ✅ Added logging for debugging
- ✅ Now properly creates multiple bookings

### Result:
- ✅ Booking 2 rooms creates 2 separate booking records
- ✅ Each booking gets its own room assignment
- ✅ All rooms properly marked as Reserved
- ✅ Response includes all bookings and assigned rooms

**The issue is now fixed! Try booking 2 rooms again and both should be created.** 🎉
