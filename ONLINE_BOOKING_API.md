# Online Booking API Documentation

This document describes the Online Booking API endpoints for external integrations (websites, mobile apps, third-party booking systems, etc.).

## Base URL
```
/api/online-booking
```

## Authentication
All endpoints require an API key in the request header:
```
X-API-Key: your-api-key-here
```

To configure the API key, set it in your `.env` file:
```
ONLINE_BOOKING_API_KEY=your-secret-api-key
```

## Endpoints

### 1. Get Available Rooms
Get a list of available rooms for specific dates.

**Endpoint:** `GET /api/online-booking/rooms/available`

**Query Parameters:**
- `check_in_date` (required): Check-in date (YYYY-MM-DD), must be at least 1 day in the future
- `check_out_date` (required): Check-out date (YYYY-MM-DD), must be after check-in date

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/online-booking/rooms/available?check_in_date=2026-01-15&check_out_date=2026-01-20" \
  -H "X-API-Key: your-api-key"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "room_id": 1,
      "room_number": "101",
      "room_type": "Deluxe",
      "base_price": 150,
      "nights": 5,
      "total_amount": 750,
      "max_guests": 2,
      "amenities": ["WiFi", "TV", "AC"]
    }
  ]
}
```

### 2. Create Booking
Create a new online booking.

**Endpoint:** `POST /api/online-booking/bookings`

**Request Body:**
```json
{
  "room_id": 1,
  "guest_name": "John Doe",
  "guest_email": "john@example.com",
  "guest_phone": "+1234567890",
  "check_in_date": "2026-01-15",
  "check_out_date": "2026-01-20",
  "number_of_guests": 2,
  "special_requests": "Late check-in requested",
  "external_booking_id": "EXT-12345",
  "webhook_url": "https://your-system.com/webhook",
  "payment_status": "paid",
  "payment_method": "Credit Card",
  "metadata": {
    "source": "website",
    "campaign": "summer2026"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "reservation_id": 123,
    "booking_reference": "BK-00000123",
    "status": "confirmed",
    "total_amount": 750,
    "check_in_date": "2026-01-15",
    "check_out_date": "2026-01-20"
  }
}
```

### 3. Update Booking Status
Update payment status or other booking information.

**Endpoint:** `POST /api/online-booking/bookings/{reservationId}/status`

**Parameters:**
- `reservationId`: Reservation ID or external booking ID

**Request Body:**
```json
{
  "payment_status": "paid",
  "external_booking_id": "EXT-12345"
}
```

**Payment Status Values:**
- `pending`: Payment pending
- `paid`: Payment completed
- `failed`: Payment failed
- `refunded`: Payment refunded

### 4. Cancel Booking
Cancel an online booking.

**Endpoint:** `POST /api/online-booking/bookings/{reservationId}/cancel`

**Parameters:**
- `reservationId`: Reservation ID or external booking ID

**Response:**
```json
{
  "success": true,
  "message": "Booking cancelled successfully",
  "data": {
    "reservation_id": 123,
    "status": "cancelled",
    "payment_status": "refunded"
  }
}
```

## Webhooks

If you provide a `webhook_url` when creating a booking, the system will send HTTP POST requests to that URL when booking status changes.

**Webhook Events:**
- `booking.created`: When a booking is created
- `booking.updated`: When booking status or payment status changes
- `booking.cancelled`: When a booking is cancelled

**Webhook Payload:**
```json
{
  "event": "booking.updated",
  "reservation_id": 123,
  "external_booking_id": "EXT-12345",
  "status": "confirmed",
  "payment_status": "paid",
  "guest_name": "John Doe",
  "guest_email": "john@example.com",
  "room_number": "101",
  "check_in_date": "2026-01-15",
  "check_out_date": "2026-01-20",
  "total_amount": 750,
  "timestamp": "2026-01-10T10:30:00Z"
}
```

## Booking Rules

### Advance Bookings
- Must be made at least 1 day in advance
- Check-in date must be in the future
- Status: `confirmed` immediately

### Online Bookings
- Must be made at least 1 day in advance
- Check-in date must be in the future
- Status: `pending` until payment is confirmed, then `confirmed`
- Supports webhook notifications
- Supports external booking ID tracking

### Walk-in Bookings
- Can be made for same day or future dates
- Status: `confirmed` immediately
- Not available via online booking API

## Error Responses

All endpoints return errors in the following format:

```json
{
  "success": false,
  "message": "Error description"
}
```

**HTTP Status Codes:**
- `200`: Success
- `201`: Created
- `400`: Bad Request (validation errors)
- `401`: Unauthorized (invalid API key)
- `404`: Not Found
- `500`: Server Error

## Integration Examples

### JavaScript/Node.js
```javascript
const axios = require('axios');

const apiKey = 'your-api-key';
const baseURL = 'https://your-domain.com/api/online-booking';

// Get available rooms
const getAvailableRooms = async (checkIn, checkOut) => {
  const response = await axios.get(`${baseURL}/rooms/available`, {
    params: { check_in_date: checkIn, check_out_date: checkOut },
    headers: { 'X-API-Key': apiKey }
  });
  return response.data.data;
};

// Create booking
const createBooking = async (bookingData) => {
  const response = await axios.post(`${baseURL}/bookings`, bookingData, {
    headers: { 'X-API-Key': apiKey }
  });
  return response.data;
};
```

### PHP
```php
$apiKey = 'your-api-key';
$baseURL = 'https://your-domain.com/api/online-booking';

// Get available rooms
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseURL . '/rooms/available?check_in_date=2026-01-15&check_out_date=2026-01-20');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: ' . $apiKey]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
```

## Support

For API support and questions, please contact your system administrator.
