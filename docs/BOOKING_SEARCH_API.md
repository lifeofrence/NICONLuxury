# Booking Search API Documentation

## Endpoint
`GET /api/admin/bookings`

## Description
This API endpoint allows you to search and filter bookings using multiple criteria. All search parameters are optional and can be combined for more specific results.

## Authentication
Requires admin authentication (Bearer token).

## Query Parameters

### Guest Information
- **`name`** (string, optional)
  - Search by guest name (partial match, case-insensitive)
  - Example: `?name=John` will match "John Doe", "Johnny Smith", etc.

- **`phone`** (string, optional)
  - Search by guest phone number (partial match)
  - Example: `?phone=555` will match any phone containing "555"

### Booking Identification
- **`booking_id`** (string/number, optional)
  - Search by booking ID
  - Supports both numeric ID (e.g., `123`) and NLA format (e.g., `NLA123`)
  - Example: `?booking_id=NLA123` or `?booking_id=123`

### Room Information
- **`room_number`** (string, optional)
  - Search by room number (partial match)
  - Example: `?room_number=101`

- **`room_type`** (string, optional)
  - Search by room type name (partial match, case-insensitive)
  - Example: `?room_type=Deluxe` will match "Deluxe Suite", "Deluxe Room", etc.

### Booking Status
- **`status`** (string, optional)
  - Filter by booking status (exact match)
  - Possible values: `pending`, `confirmed`, `checked_in`, `checked_out`, `cancelled`
  - Example: `?status=confirmed`

### Date Filters
- **`check_in_date`** (date, optional)
  - Filter by exact check-in date
  - Format: `YYYY-MM-DD`
  - Example: `?check_in_date=2025-12-20`

- **`check_out_date`** (date, optional)
  - Filter by exact check-out date
  - Format: `YYYY-MM-DD`
  - Example: `?check_out_date=2025-12-25`

- **`check_in_from`** (date, optional)
  - Find bookings that check out after this date
  - Useful for finding active bookings during a period
  - Format: `YYYY-MM-DD`
  - Example: `?check_in_from=2025-12-20`

- **`check_out_to`** (date, optional)
  - Find bookings that check in before this date
  - Useful for finding active bookings during a period
  - Format: `YYYY-MM-DD`
  - Example: `?check_out_to=2025-12-25`

## Response Format

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 123,
      "room_id": 5,
      "room_type_id": 2,
      "guest_name": "John Doe",
      "guest_email": "john@example.com",
      "guest_phone": "+1234567890",
      "check_in_date": "2025-12-20",
      "check_out_date": "2025-12-25",
      "status": "confirmed",
      "payment_reference": "PAY123456",
      "amount": "500.00",
      "created_at": "2025-12-18T10:00:00.000000Z",
      "updated_at": "2025-12-18T10:00:00.000000Z",
      "room_type": {
        "id": 2,
        "name": "Deluxe Suite",
        "description": "...",
        "price_per_night": "100.00",
        "max_occupancy": 2
      },
      "room": {
        "id": 5,
        "room_number": "101",
        "room_type_id": 2,
        "status": "occupied"
      }
    }
  ],
  "first_page_url": "http://example.com/api/admin/bookings?page=1",
  "from": 1,
  "last_page": 5,
  "last_page_url": "http://example.com/api/admin/bookings?page=5",
  "links": [...],
  "next_page_url": "http://example.com/api/admin/bookings?page=2",
  "path": "http://example.com/api/admin/bookings",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 100
}
```

## Usage Examples

### 1. Search by Guest Name
```bash
GET /api/admin/bookings?name=John
```

### 2. Search by Phone Number
```bash
GET /api/admin/bookings?phone=555-1234
```

### 3. Search by NLA ID
```bash
GET /api/admin/bookings?booking_id=NLA123
```

### 4. Search by Room Number
```bash
GET /api/admin/bookings?room_number=101
```

### 5. Search by Room Type
```bash
GET /api/admin/bookings?room_type=Deluxe
```

### 6. Filter by Status
```bash
GET /api/admin/bookings?status=confirmed
```

### 7. Filter by Check-in Date
```bash
GET /api/admin/bookings?check_in_date=2025-12-20
```

### 8. Filter by Check-out Date
```bash
GET /api/admin/bookings?check_out_date=2025-12-25
```

### 9. Find Bookings Active During a Period
```bash
GET /api/admin/bookings?check_in_from=2025-12-20&check_out_to=2025-12-25
```

### 10. Combined Search (Multiple Criteria)
```bash
GET /api/admin/bookings?name=John&status=confirmed&room_type=Deluxe
```

### 11. Search with Pagination
```bash
GET /api/admin/bookings?name=John&page=2
```

## Notes

- All text searches (name, phone, room_number, room_type) use partial matching and are case-insensitive
- The booking_id parameter accepts both formats: numeric (123) or with NLA prefix (NLA123)
- Results are paginated with 20 items per page by default
- Results are ordered by booking ID in descending order (newest first)
- Multiple search parameters can be combined for more specific results
- The response includes related room and room_type data for convenience
