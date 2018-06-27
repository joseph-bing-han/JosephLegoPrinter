
#include <Stepper.h>

// change this to the number of steps on your motor
#define STEPS 100
const byte analogInPin = 4;
Stepper stepper(STEPS, 0, 1, 2, 3);
unsigned long analogIn;
void setup()
{
    // set the speed of the motor to 50 RPMs
    stepper.setSpeed(360);
    pinMode(analogInPin, INPUT);
}

void loop()
{
    analogIn = pulseIn(analogInPin, HIGH);

    if (analogIn > 1000 && analogIn < 5000)
    {
        stepper.step(14); //4步模式下旋转一周用2048 步。
    }
}
