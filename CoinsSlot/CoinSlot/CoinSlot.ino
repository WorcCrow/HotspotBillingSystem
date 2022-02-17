
int pinRead = 13;
int t = 1;
void setup() {
  Serial.begin(9600);
  pinMode(pinRead, INPUT);
}
void loop() {
  int pinState = digitalRead(pinRead);
  if(pinState != t){
    t = pinState;
    if(pinState == 0){
         Serial.println("Worc");
         delay(50);
    }
  }
  delay(1);
}
