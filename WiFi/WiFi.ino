#include <ESP8266WiFi.h>;
#include <ESP8266WebServer.h>
#include <WiFiUdp.h>
#include <Servo.h>

ESP8266WebServer http_server(80);
WiFiUDP udp_server;
Servo pen_servo; // create servo object to control a servo
unsigned int localPort = 9000;

const char *ap_ssid = "JosephWiFi";
const char *ap_password = "12347890";

// buffers for receiving and sending data
char packetBuffer[UDP_TX_PACKET_MAX_SIZE]; //buffer to hold incoming packet,
const char *replyBuffer = "OK";

const byte pin_x = 1;
const byte pin_y = 0;
const byte pin_z = 3;
const byte pin_check = 2;

void handleRoot()
{
  http_server.send(200, "text/html", "<html><body>IP:" + WiFi.localIP().toString() + "</body></html>0.");
}

void setup()
{
  // 必须采用 AP 与 Station 兼容模式
  WiFi.mode(WIFI_AP_STA);
  delay(500);
  WiFi.softAP(ap_ssid, ap_password);
  delay(500);
  // 等待配网
  WiFi.beginSmartConfig();

  // 收到配网信息后ESP8266将自动连接，WiFi.status 状态就会返回：已连接
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(500);
  }

  // Start the http_server
  http_server.begin();
  http_server.on("/", handleRoot);
  udp_server.begin(localPort);

  pinMode(pin_x, OUTPUT);
  pinMode(pin_y, OUTPUT);
  pinMode(pin_z, OUTPUT);
  pinMode(pin_check, OUTPUT);

  digitalWrite(pin_x, LOW);
  digitalWrite(pin_y, LOW);
  digitalWrite(pin_z, LOW);
  digitalWrite(pin_check, LOW);

  pen_servo.attach(pin_z);
  pen_servo.write(0);

  pinMode(pin_check, INPUT);
}
void controllServo()
{
  if (packetBuffer[0] == 123)
  {
    int servo = packetBuffer[1];
    int step = packetBuffer[2];

    if (servo == 1)
    {
      if (step < 150)
      {
        for (int i = 0; i < step; i++)
        {
          digitalWrite(pin_x, HIGH);
          delay(3);
          digitalWrite(pin_x, LOW);
          delay(300);
        }
        pinMode(pin_check, OUTPUT);
        digitalWrite(pin_check, LOW);
      }
      else
      {
        pinMode(pin_check, INPUT);
        step = step - 256;
        for (int i = step; i < 0; i++)
        {
          if (digitalRead(pin_check) == HIGH)
          {
            break;
          }
          else
          {
            digitalWrite(pin_x, HIGH);
            delay(8);
            digitalWrite(pin_x, LOW);
            delay(300);
          }
        }
      }

      delay(100);
    }
    else if (servo == 2)
    {
      digitalWrite(pin_y, HIGH);
      delay(3);
      digitalWrite(pin_y, LOW);
      delay(500);
    }
    else if (servo == 3)
    {
      if (step < 150)
      {
        pen_servo.write(48);
        delay(400);
      }
      else if (step > 150)
      {
        pen_servo.write(0);
        delay(400);
      }
    }
  }
}
void loop()
{
  int packetSize = udp_server.parsePacket();
  if (packetSize)
  {

    // read the packet into packetBufffer
    udp_server.read(packetBuffer, UDP_TX_PACKET_MAX_SIZE);

    controllServo();

    // send a reply, to the IP address and port that sent us the packet we received
    udp_server.beginPacket(udp_server.remoteIP(), udp_server.remotePort());
    udp_server.write(replyBuffer);
    udp_server.endPacket();
  }
  http_server.handleClient();
}
