#!/usr/bin/env python

import requests
import serial
import json
import os


class SerialComm:
    def __init__(self):
        # serial initialize
        self.ser = serial.Serial('/dev/ttyUSB0', 9600, timeout=1)
        self.domain = "http://127.0.0.1:8082"
        self.token = "1f45f5d94a0226d1eec541da180fb03eb39170b8"
        self.receieved = ""
        self.voice = "-ven-us+f3 -s120"

        self.headers = {
            'Authorization': self.token,
            'Content-type': 'application/json',
        }

    def send_message(self, passengerType):
        try:
            params = {
                'passenger_status': 'take_the_bus',
                'passenger_type': passengerType,
            }
            params = json.dumps(params)

            response = requests.post(
                self.domain+"/api/v1/data/log",
                data=params,
                headers={
                    'Authorization': self.token,
                    'Content-type': 'application/json',
                },
            )

            # try:
            #     jsonData = json.loads(response.text)
            # except Exception as e:
            #     print(e)

            print(response.text)
        except Exception as e:
            print(e)
            print('Something went wrong, please check the error.')
            pass

    def speak(self, sentence):
        os.system("espeak-ng " + self.voice + " \"" + sentence + ".\"")


comm = SerialComm()

print("Serial initialize, done.")

comm.speak("System is ready")

# ser.write('{"token":"' + token + '","username":"' + sys.argv[1] + '","uid":"' + sys.argv[2] + '","action":"insert"}')

while 1:
    receieved = comm.ser.readline().decode().strip()

    if receieved:
        print(receieved)
        if 'button1 is pressed' in receieved:
            comm.speak("Button 1 was pressed for student fare.")
            comm.send_message('student')

        if 'button2 is pressed' in receieved:
            comm.speak("Button 2 was pressed for senior citizen fare.")
            comm.send_message('senior_citizen')

        if 'button3 is pressed' in receieved:
            comm.speak("Button 3 was pressed for regular fare.")
            comm.send_message('regular')

        comm.speak("Please wait for a moment as your request is being processed!")
