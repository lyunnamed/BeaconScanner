# This is a working prototype. DO NOT USE IT IN LIVE PROJECTS
import ScanUtility
import bluetooth._bluetooth as bluez
import time
import requests
import json
import sys
import logging
from collections import defaultdict
from queue import Queue
from threading import Thread
from datetime import datetime

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('beacon_scanner.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# Configuration
PHP_ENDPOINT = "http://10.42.0.1/process_beacon.php"
TARGET_UUID = "b9407f30-f5f8-466e-aff9-25556b57fe6d"  # Estimote UUID

# Cache for recent scans and pending uploads
recent_scans = defaultdict(float)  # {minor: timestamp}
pending_queue = Queue()  # 存储待上传的数据
SCAN_INTERVAL = 2  # 2 seconds interval
MAX_RETRIES = 3  # 最大重试次数

def upload_worker():
    """后台线程处理数据上传"""
    logger.info("Upload worker thread started")
    while True:
        try:
            minor, timestamp, retries = pending_queue.get()
            if retries >= MAX_RETRIES:
                logger.error(f"Failed to upload minor={minor} after {MAX_RETRIES} attempts")
                continue

            payload = {
                'minor': minor,
                'timestamp': timestamp
            }
            logger.debug(f"Attempting to upload minor={minor} (attempt {retries + 1}/{MAX_RETRIES})")
            response = requests.post(PHP_ENDPOINT, data=payload)
            
            if response.status_code != 200:
                logger.warning(f"Upload failed for minor={minor}, status={response.status_code}, retrying... ({retries + 1}/{MAX_RETRIES})")
                pending_queue.put((minor, timestamp, retries + 1))
            else:
                logger.info(f"Successfully uploaded minor={minor}, response: {response.text}")
                
        except Exception as e:
            logger.error(f"Error in upload worker: {e}", exc_info=True)
            pending_queue.put((minor, timestamp, retries + 1))
        finally:
            pending_queue.task_done()

def send_to_php(minor, timestamp):
    """将数据加入上传队列"""
    current_time = time.time()
    last_scan_time = recent_scans.get(minor, 0)
    time_diff = current_time - last_scan_time
    
    if time_diff > SCAN_INTERVAL:
        recent_scans[minor] = current_time
        pending_queue.put((minor, timestamp, 0))  # 0 表示重试次数
        logger.info(f"Queued minor={minor} for upload (last scan was {time_diff:.2f}s ago)")
    else:
        logger.debug(f"Skipped minor={minor}: Too recent ({time_diff:.2f}s < {SCAN_INTERVAL}s)")

# 启动上传工作线程
upload_thread = Thread(target=upload_worker, daemon=True)
upload_thread.start()

# Initialize the BLE radio
dev_id = 0  # use hci0
try:
    sock = bluez.hci_open_dev(dev_id)
    logger.info("BLE radio initialized successfully")
except Exception as e:
    logger.critical(f"Error accessing bluetooth device: {e}", exc_info=True)
    sys.exit(1)

# Enable scanning
ScanUtility.hci_enable_le_scan(sock)
logger.info("Starting beacon scan...")

while True:
    try:
        returnedList = ScanUtility.parse_events(sock, 10)
        if returnedList:  # Check if list is not empty
            for beacon in returnedList:
                if isinstance(beacon, dict) and 'uuid' in beacon:
                    if beacon['uuid'].lower() == TARGET_UUID.lower():
                        minor = beacon['minor']
                        timestamp = time.time()
                        logger.debug(f"Detected beacon: minor={minor}, uuid={beacon['uuid']}")
                        send_to_php(minor, timestamp)
    except Exception as e:
        logger.error(f"Error during scanning: {e}", exc_info=True)
        time.sleep(1)
