#!/bin/bash

# Booking Search API Test Examples
# Make sure to replace YOUR_API_TOKEN with your actual admin token

API_BASE_URL="http://localhost:8000/api"
# For production: API_BASE_URL="https://niconluxury.jubileesystem.com/api"

TOKEN="YOUR_API_TOKEN"

echo "=== Booking Search API Test Examples ==="
echo ""

# Test 1: Search by guest name
echo "1. Search by guest name (John):"
curl -X GET "$API_BASE_URL/admin/bookings?name=John" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 2: Search by phone number
echo "2. Search by phone number (555):"
curl -X GET "$API_BASE_URL/admin/bookings?phone=555" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 3: Search by NLA ID
echo "3. Search by NLA ID (NLA123):"
curl -X GET "$API_BASE_URL/admin/bookings?booking_id=NLA123" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 4: Search by room number
echo "4. Search by room number (101):"
curl -X GET "$API_BASE_URL/admin/bookings?room_number=101" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 5: Search by room type
echo "5. Search by room type (Deluxe):"
curl -X GET "$API_BASE_URL/admin/bookings?room_type=Deluxe" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 6: Filter by status
echo "6. Filter by status (confirmed):"
curl -X GET "$API_BASE_URL/admin/bookings?status=confirmed" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 7: Filter by check-in date
echo "7. Filter by check-in date (2025-12-20):"
curl -X GET "$API_BASE_URL/admin/bookings?check_in_date=2025-12-20" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 8: Filter by check-out date
echo "8. Filter by check-out date (2025-12-25):"
curl -X GET "$API_BASE_URL/admin/bookings?check_out_date=2025-12-25" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 9: Combined search (multiple criteria)
echo "9. Combined search (name + status + room type):"
curl -X GET "$API_BASE_URL/admin/bookings?name=John&status=confirmed&room_type=Deluxe" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 10: Date range search
echo "10. Find bookings active during a period:"
curl -X GET "$API_BASE_URL/admin/bookings?check_in_from=2025-12-20&check_out_to=2025-12-25" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

echo "=== Tests Complete ==="
