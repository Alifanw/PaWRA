#!/usr/bin/env python3
"""
Mock Doorlock Server for Testing
Simulates Raspberry Pi Flask doorlock API
Run: python3 mock_doorlock.py
"""

from flask import Flask, request, jsonify
import time

app = Flask(__name__)

# Mock token (sama dengan API)
VALID_TOKEN = "SECURE_KEY_IGASAR"

@app.route('/door/open', methods=['POST'])
def open_door():
    """Simulate door open trigger"""
    
    # Check authorization
    auth_header = request.headers.get('Authorization', '')
    if auth_header != f'Bearer {VALID_TOKEN}':
        return jsonify({
            'status': 'error',
            'message': 'Invalid token'
        }), 401
    
    # Get delay from request
    data = request.get_json() or {}
    delay = data.get('delay', 3)
    
    # Simulate door trigger
    print(f"[MOCK] Door triggered! Delay: {delay}s")
    
    return jsonify({
        'status': 'success',
        'message': f'Door opened for {delay} seconds',
        'timestamp': time.strftime('%Y-%m-%d %H:%M:%S')
    })

@app.route('/door/status', methods=['GET'])
def door_status():
    """Get door status"""
    return jsonify({
        'status': 'success',
        'door_status': 'closed',
        'server': 'mock',
        'timestamp': time.strftime('%Y-%m-%d %H:%M:%S')
    })

@app.route('/health', methods=['GET'])
def health():
    """Health check"""
    return jsonify({
        'status': 'healthy',
        'server': 'mock_doorlock',
        'version': '1.0'
    })

if __name__ == '__main__':
    print("=" * 50)
    print("MOCK DOORLOCK SERVER")
    print("=" * 50)
    print("URL: http://localhost:5000")
    print(f"Token: {VALID_TOKEN}")
    print("-" * 50)
    app.run(host='0.0.0.0', port=5000, debug=True)
