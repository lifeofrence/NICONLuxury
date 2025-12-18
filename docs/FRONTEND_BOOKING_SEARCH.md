# Frontend Booking Search Implementation

## Overview
Added a comprehensive advanced search modal to the booking list component in the admin panel. The search supports all the backend API parameters.

## Files Modified/Created

### 1. **`frontend/components/admin/booking-search-modal.tsx`** (NEW)
A reusable search modal component with:
- Guest information filters (name, phone)
- Booking identification (booking ID with NLA support, status)
- Room information (room number, room type)
- Date filters (check-in, check-out dates)
- Active filter count display
- Clear all filters functionality

### 2. **`frontend/app/admin/bookings/booking-actions.ts`** (MODIFIED)
- Added `BookingSearchFilters` interface
- Updated `getBookings()` function to accept filters object
- Filters are passed as URL query parameters to the backend API

### 3. **`frontend/app/admin/bookings/page.tsx`** (MODIFIED)
- Added `searchParams` prop handling
- Extracts all search filters from URL parameters
- Passes filters to `getBookings()` function

### 4. **`frontend/components/admin/booking-list.tsx`** (MODIFIED)
- Replaced simple search bar with "Advanced Search" button
- Shows active filter count badge
- Displays active filters below search button
- Added "Clear Filters" button when filters are active
- Integrated `BookingSearchModal` component

## Features

### Advanced Search Button
- Shows "Advanced Search" with filter icon
- Displays badge with active filter count
- Opens comprehensive search modal on click

### Active Filters Display
- Shows all active filters with their values
- Provides quick "Clear Filters" button
- Filters persist across page navigation

### Search Modal
Organized into sections:
1. **Guest Information**
   - Guest Name (partial match)
   - Phone Number (partial match)

2. **Booking Information**
   - Booking ID (supports NLA123 or 123 format)
   - Status dropdown (all, pending, confirmed, checked_in, checked_out, cancelled)

3. **Room Information**
   - Room Number (partial match)
   - Room Type (partial match)

4. **Date Filters**
   - Check-in Date (date picker)
   - Check-out Date (date picker)

### User Experience
- All search fields are optional
- Partial matching for text fields (case-insensitive)
- Active filter count shown on search button
- Filters persist in URL (shareable, bookmarkable)
- Clear individual or all filters
- Responsive design

## Usage

### For Users
1. Click "Advanced Search" button
2. Fill in any combination of search criteria
3. Click "Search" to apply filters
4. View results with active filters displayed
5. Click "Clear Filters" to reset

### For Developers
The search modal can be reused in other components:

```tsx
import { BookingSearchModal, BookingSearchFilters } from './booking-search-modal'

const [isSearchOpen, setIsSearchOpen] = useState(false)

const handleSearch = (filters: BookingSearchFilters) => {
  // Handle search with filters
}

<BookingSearchModal
  open={isSearchOpen}
  setOpen={setIsSearchOpen}
  onSearch={handleSearch}
  initialFilters={{}} // Optional initial values
/>
```

## API Integration

The frontend now sends all search parameters to:
```
GET /api/admin/bookings?name=John&status=confirmed&room_type=Deluxe&page=1
```

All parameters are optional and can be combined for precise filtering.

## Testing

To test the search functionality:
1. Navigate to `/admin/bookings`
2. Click "Advanced Search"
3. Try different filter combinations:
   - Search by guest name
   - Filter by booking status
   - Search by room number
   - Combine multiple filters
4. Verify results update correctly
5. Test "Clear Filters" functionality
6. Check URL updates with search parameters

## Future Enhancements

Potential improvements:
- Add date range filters (check_in_from, check_out_to)
- Save favorite search filters
- Export filtered results
- Quick filter presets (e.g., "Check-ins Today", "Pending Bookings")
- Search history
