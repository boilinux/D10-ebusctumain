#include <Arduino.h>

#define BUTTON1 A0
#define BUTTON2 A1
#define BUTTON3 A2

byte btn_state = 0, btn_oldstate = 0;
byte btn_state2 = 0, btn_oldstate2 = 0;
byte btn_state3 = 0, btn_oldstate3 = 0;

void setup()
{
    Serial.begin(9600);

    // Initialize pinMode
    pinMode(BUTTON1, INPUT);
    pinMode(BUTTON2, INPUT);
    pinMode(BUTTON3, INPUT);
}
void loop()
{
    btn_state = digitalRead(BUTTON1);
    btn_state2 = digitalRead(BUTTON2);
    btn_state3 = digitalRead(BUTTON3);
    if (btn_state == LOW && btn_state != btn_oldstate)
    {
        Serial.print("button1 is pressed");
    }
    if (btn_state2 == LOW && btn_state2 != btn_oldstate2)
    {
        Serial.print("button2 is pressed");
    }
    if (btn_state3 == LOW && btn_state3 != btn_oldstate3)
    {
        Serial.print("button3 is pressed");
    }
    btn_oldstate = btn_state;
    btn_oldstate2 = btn_state2;
    btn_oldstate3 = btn_state3;
}
