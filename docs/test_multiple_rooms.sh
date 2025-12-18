#!/bin/bash

# Test Multiple Room Booking
# This script tests booking 2 rooms to verify both are created

API_URL="${API_URL:-http://localhost:8000/api}"
TOKEN="YOUR_TOKEN_HERE"

echo "=== Testing Multiple Room Booking ==="
echo ""
echo "Booking 2 rooms for the same guest..."
echo ""

# Make a booking request for 2 rooms
curl -X POST "$API_URL/bookings" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "guest_name": "Test Guest",
    "guest_email": "test@example.com",
    "guest_phone": "+1234567890",
    "room_type_id": 1,
    "check_in_date": "2025-12-20",
    "check_out_date": "2025-12-22",
    "number_of_rooms": 2
  }' | jq '.'

echo ""
echo "=== Check the response above ==="
echo "You should see:"
echo "  - number_of_rooms: 2"
echo "  - bookings array with 2 items"
echo "  - assigned_rooms array with 2 items"
echo "  - Each booking should have a different room_id"
