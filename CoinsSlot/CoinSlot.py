import serial
import requests
import os
payload = {'mode': 'coins', 'amount': '1'}
ser = serial.Serial("COM3")
os.system("cls")
print("Welcome to Worc Wifi Vendo")
print("System Ready")
while 1:
    s = ser.readline()
    if s != b'':
        if s == b'Worc\r\n':
            requests.post("http://timer/hotspot.php", data=payload)
            print("Coin Inserted")
