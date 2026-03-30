# Sample CSV Files for Bulk QR Code Generation

This directory contains sample CSV files to test the bulk QR code generation feature.

## Available Sample Files:

### 1. sample_urls.csv
**QR Code Type:** URL
**Format:** One column with URLs
**Rows:** 10 sample URLs including popular websites

### 2. sample_emails.csv
**QR Code Type:** Email
**Format:** email,subject,body
**Rows:** 10 sample email combinations
**Use:** Creates mailto: QR codes for email, subject, and message

### 3. sample_phones.csv
**QR Code Type:** Phone
**Format:** One column with phone numbers
**Rows:** 10 sample phone numbers
**Use:** Creates tel: QR codes for direct calling

### 4. sample_sms.csv
**QR Code Type:** SMS
**Format:** phone,message
**Rows:** 10 sample SMS combinations
**Use:** Creates smsto: QR codes for pre-filled text messages

### 5. sample_wifi.csv
**QR Code Type:** WiFi
**Format:** ssid,password,security
**Rows:** 10 sample WiFi configurations
**Use:** Creates WiFi QR codes for easy network connection

### 6. sample_text.csv
**QR Code Type:** Text
**Format:** One column with text messages
**Rows:** 10 sample text messages
**Use:** Creates simple text QR codes

## How to Use:

1. Go to: http://localhost/qr-code.io/bulk
2. Login to your account
3. Upload any of these sample CSV files
4. Select the appropriate QR code type
5. Customize appearance (colors, size, etc.)
6. Generate QR codes
7. Download results as ZIP or CSV

## Testing Recommendations:

- Start with sample_urls.csv (simplest format)
- Try sample_emails.csv to test multi-column format
- Test sample_wifi.csv for complex QR code types
- Use small files first to verify the system works

## File Locations:

All sample files are located in: c:\xampp\htdocs\qr-code.io\

Ready to test bulk QR code generation! 🚀
