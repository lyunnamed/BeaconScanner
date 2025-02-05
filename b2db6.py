# This is a working prototype. DO NOT USE IT IN LIVE PROJECTS
import ScanUtility
import bluetooth._bluetooth as bluez
import time
import requests
import json
import sys

# Configuration
PHP_ENDPOINT = "http://ip_address/process_beacon.php"
TARGET_UUID = "b9407f30-f5f8-466e-aff9-25556b57fe6d"  # Estimote UUID

def send_to_php(minor, timestamp):
    try:
        payload = {
            'minor': minor,
            'timestamp': timestamp
        }
        print(f"Sending data to {PHP_ENDPOINT} with minor={minor}")
        response = requests.post(PHP_ENDPOINT, data=payload)
        print(f"Response: {response.text}")
    except Exception as e:
        print(f"Error sending data: {e}")

# Initialize the BLE radio
dev_id = 0  # use hci0
try:
    sock = bluez.hci_open_dev(dev_id)
    print("BLE radio initialized")
except:
    print("Error accessing bluetooth device...")
    sys.exit(1)

# Enable scanning
ScanUtility.hci_enable_le_scan(sock)

print("Starting beacon scan...")

while True:
    try:
        returnedList = ScanUtility.parse_events(sock, 10)
        if returnedList:  # Check if list is not empty
            for beacon in returnedList:
                if isinstance(beacon, dict) and 'uuid' in beacon:
                    if beacon['uuid'].lower() == TARGET_UUID.lower():
                        minor = beacon['minor']
                        timestamp = time.time()
                        send_to_php(minor, timestamp)
    except Exception as e:
        print(f"Error during scanning: {e}")
        time.sleep(1)
