# Booking Management Enhancements

## Backend Changes

### 1. Enhanced Update Booking Endpoint

**Endpoint:** `PUT /api/admin/bookings/{id}`

#### New Features:
- **Room Assignment Change**: Can now change the assigned room for a booking
- **Automatic Room Status Management**: Handles old and new room statuses automatically
- **Validation**: Ensures new room is available before assignment

#### Request Body:
```json
{
  "status": "confirmed",
  "guest_name": "John Doe",
  "guest_email": "john@example.com",
  "guest_phone": "+1234567890",
  "room_id": 5,  // NEW: Can change assigned room
  "room_type_id": 2
}
```

#### Room Change Logic:
1. **Old Room**: Automatically set to "Available" when changed
2. **New Room**: 
   - Checked for availability
   - Set to "Reserved" if booking is pending
   - Set to "Occupied" if booking is confirmed
3. **Validation**: Returns 422 error if new room is not available

#### Status Change Logic:
- **Cancelled**: Room status → "Available"
- **Confirmed**: Room status → "Occupied"

---

### 2. New Confirm Booking Endpoint

**Endpoint:** `POST /api/admin/bookings/{id}/confirm`

#### Purpose:
Quickly confirm a pending booking with a single action.

#### Requirements:
- Booking must have status "pending"
- Returns 400 error if booking is not pending

#### Actions:
1. Updates booking status to "confirmed"
2. Updates assigned room status to "Occupied"
3. Returns updated booking with room and room type data

#### Response:
```json
{
  "message": "Booking confirmed successfully",
  "booking": {
    "id": 123,
    "status": "confirmed",
    "guest_name": "John Doe",
    "room": {
      "id": 5,
      "room_number": "101",
      "status": "Occupied"
    },
    "room_type": {
      "id": 2,
      "name": "Deluxe Suite"
    }
  }
}
```

---

## Frontend Integration Guide

### 1. Update Booking Form Component

The booking form needs to be updated to include room selection.

#### Add Room Selection Field:

```tsx
// In booking-form.tsx

// Add state for available rooms
const [availableRooms, setAvailableRooms] = useState<Room[]>([])

// Fetch available rooms for the booking's room type
useEffect(() => {
  if (booking?.room_type_id) {
    fetchAvailableRooms(booking.room_type_id)
  }
}, [booking?.room_type_id])

// Add to form:
<div className="space-y-2">
  <Label htmlFor="room_id">Assigned Room</Label>
  <Select
    name="room_id"
    value={booking?.room_id?.toString() || ''}
    onValueChange={(value) => {
      // Update form state
    }}
  >
    <SelectTrigger>
      <SelectValue placeholder="Select room" />
    </SelectTrigger>
    <SelectContent>
      <SelectItem value="">No room assigned</SelectItem>
      {availableRooms.map((room) => (
        <SelectItem key={room.id} value={room.id.toString()}>
          Room {room.room_number} - {room.status}
        </SelectItem>
      ))}
    </SelectContent>
  </Select>
</div>
```

#### Update the updateBooking action:

```typescript
// In booking-actions.ts

export async function updateBooking(id: number, prevState: any, formData: FormData) {
    const token = await getAuthToken()
    if (!token) return { message: 'Not authenticated', success: false }

    const rawFormData: any = {
        guest_name: formData.get('guest_name'),
        guest_email: formData.get('guest_email'),
        guest_phone: formData.get('guest_phone'),
        status: formData.get('status'),
        room_id: formData.get('room_id') || null, // NEW: Include room_id
    }

    // ... rest of the function
}
```

---

### 2. Add Confirm Button to Booking List

#### In booking-list.tsx, add confirm action:

```tsx
// Add confirm function
async function handleConfirm(id: number) {
    if (!confirm('Confirm this booking? This will mark it as confirmed and update the room status.')) return
    setLoadingId(id)
    const result = await confirmBooking(id)
    setLoadingId(null)
    if (result.success) {
        router.refresh()
    } else {
        alert(result.message)
    }
}

// In the dropdown menu, add confirm option for pending bookings:
{booking.status === 'pending' && (
    <>
        <DropdownMenuItem
            onClick={() => handleConfirm(booking.id)}
            className="text-green-600"
        >
            <Check className="mr-2 h-4 w-4" />
            Confirm Booking
        </DropdownMenuItem>
        <DropdownMenuSeparator />
    </>
)}
```

#### Create confirmBooking action:

```typescript
// In booking-actions.ts

export async function confirmBooking(id: number) {
    const token = await getAuthToken()
    if (!token) return { message: 'Not authenticated', success: false }

    try {
        const res = await fetch(`${API_URL}/api/admin/bookings/${id}/confirm`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        })

        const data = await res.json()

        if (!res.ok) {
            return { message: data.message || 'Failed to confirm booking', success: false }
        }

        revalidatePath('/admin/bookings')
        revalidatePath('/admin')
        return { message: 'Booking confirmed successfully', success: true }
    } catch (error) {
        return { message: 'An error occurred', success: false }
    }
}
```

---

## Testing

### Test Room Assignment Change:
1. Go to a booking
2. Click "Edit Details"
3. Change the assigned room
4. Save
5. Verify:
   - Old room status is "Available"
   - New room status is "Reserved" or "Occupied"
   - Booking shows new room

### Test Confirm Booking:
1. Find a booking with status "pending"
2. Click actions menu → "Confirm Booking"
3. Verify:
   - Booking status changes to "confirmed"
   - Room status changes to "Occupied"
   - Success message displayed

### Test API Endpoints:

```bash
# Confirm a pending booking
curl -X POST "http://localhost:8000/api/admin/bookings/123/confirm" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Update booking with room change
curl -X PUT "http://localhost:8000/api/admin/bookings/123" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "confirmed",
    "guest_name": "John Doe",
    "guest_email": "john@example.com",
    "guest_phone": "+1234567890",
    "room_id": 5
  }'
```

---

## Summary

### Backend ✅ Complete:
- ✅ Enhanced update endpoint to support room changes
- ✅ Added room status management logic
- ✅ Created confirm booking endpoint
- ✅ Added route for confirm endpoint

### Frontend 🔧 Needs Implementation:
- 🔧 Add room selection to booking form
- 🔧 Add confirm button to booking list
- 🔧 Create confirmBooking action
- 🔧 Fetch available rooms for selection

The backend is ready. Once the frontend directory is accessible, these changes can be implemented following the guide above.
