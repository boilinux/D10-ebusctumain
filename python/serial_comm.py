#!/usr/bin/env python

import time
import http.client as httplib
import urllib
import sys
import serial
import json
import os

class SerialComm :
    def __init__(self):
        #serial initialize
        self.ser = serial.Serial('/dev/ttyUSB0', 9600, timeout=1)
        self.domain = "http://127.0.0.1:8082"
        self.token = "1f45f5d94a0226d1eec541da180fb03eb39170b8"
        self.receieved = ""
        self.voice = "-ven-us+f3 -s120"

        self.headers = {
            "Content-type": "application/x-www-form-urlencoded",
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Charset': 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
        }

	# Send credit to localhost.
    def send_credit (uid, amount):
        global token
        global domain
        global headers

        params = urllib.urlencode({
            "token" : token,
            "amount" : amount,
            "uid" : uid,
        })

        httpr = httplib.HTTPConnection(domain)
        httpr.request("POST", "/api/v1/data/log", params, headers)

        result = httpr.getresponse()

        print (result.status, result.reason)

        sys.exit()

    def send_activity (uid, op):
        global token
        global domain
        global headers

        params = urllib.urlencode({
            "token" : token,
            "op" : op,
            "uid" : uid,
        })

        httpr = httplib.HTTPConnection(domain)
        httpr.request("POST", "/api/data/create_activity", params, headers)

        result = httpr.getresponse()

        print (result.status, result.reason)

        sys.exit()

    def speak(self,sentence):
        os.system("espeak-ng " + self.voice + " \""+ sentence +".\"")

comm = SerialComm()

print ("Serial initialize, done.")

comm.speak("System is ready")

# ser.write('{"token":"' + token + '","username":"' + sys.argv[1] + '","uid":"' + sys.argv[2] + '","action":"insert"}')

while 1:
    receieved = comm.ser.readline().decode().strip()

    if receieved:
        print (receieved)
        if 'button1 is pressed' in receieved:
            comm.speak("Button 1 was pressed")

        if 'button2 is pressed' in receieved:
            comm.speak("Button 2 was pressed")

        if 'button3 is pressed' in receieved:
            comm.speak("Button 3 was pressed")

        comm.speak("Please wait for a moment as your request is being processed!")
