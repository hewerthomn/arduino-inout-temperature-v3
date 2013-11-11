
/*
* ----------------------------------------------------------------------------
* "THE BEER-WARE LICENSE" (Revision 42):
* <phk@FreeBSD.ORG> wrote this file. As long as you retain this notice you
* can do whatever you want with this stuff. If we meet some day, and you think
* this stuff is worth it, you can buy me a beer in return Poul-Henning Kamp
* ----------------------------------------------------------------------------
*/
/*
* InOut Temperature V3
* 
*  
* This sketch reads the temperature and humidity from DHT11`s sensor,
* connects to a website using an Arduino Wiznet Ethernet shield,
* and display content in a Nokia 5110 LCD display. 
* 
* Circuit:
*  Ethernet shield attached to pins 10, 11, 12, 13
*  Nokia 5110 LCD 86x86 attachedto pins 2, 3, 4, 5, 6, 7
*  Sensor DHT11 attached to pin 9
*/
#include <Adafruit_GFX.h>
#include <Adafruit_PCD8544.h>
#include <dht11.h>
#include <SPI.h>
#include <Ethernet.h>

char InPlacename[] = "@My room";

// pin 7 - Serial clock out (SCLK)
// pin 6 - Serial data out (DIN)
// pin 5 - Data/Command select (D/C)
// pin 4 - LCD chip select (CS)
// pin 3 - LCD reset (RST)
Adafruit_PCD8544 lcd = Adafruit_PCD8544(7, 6, 5, 4, 3);

// sensor data
int InTemperature  = 0;
int InHumidity     = 0;

dht11 DHT11;
#define DHT11PIN 9

long updateFrequency = 5 * 60000; // 5 minutes

EthernetClient client;
byte mac[]    = { 0xDE, 0xAD, 0xBA, 0xEF, 0xFE, 0xBA };
IPAddress ip(10, 1, 1, 10);
IPAddress server(10, 1, 1, 6);
//char server[] = "hewertho.mn";

// variables for content
char Date[]           = "Jan,01";
char Time[]           = "00:00";
char OutPlacename[16];
char OutTemperature[] = "00";
char OutHumidity[]    = "00%";
char OutDewPoint[]    = "00.00";

boolean hasStats = false; // until this is true default text will be displayed

void setup()
{
  lcd.begin();
  lcd.setContrast(60);
  lcd.clearDisplay();
  
  printDefaultText();
  showTemperatures();
  
  Ethernet.begin(mac, ip);

  delay(2000);
}

void loop()
{
  getOutTemperature();
  showTemperatures();  

  delay(updateFrequency);
}

void printDefaultText()
{
  lcd.clearDisplay();
  lcd.setTextSize(1);
  lcd.println("InOut");
  lcd.print("Temperature ");
  lcd.setTextColor(WHITE, BLACK);
  lcd.print("v3");
  lcd.setTextColor(BLACK);
  lcd.println("");
  lcd.println(InPlacename);
  lcd.println("");
  lcd.println(OutPlacename);  
  lcd.display();
  
  delay(3000);
  lcd.clearDisplay();
}

void showTemperatures()
{
  int chk = DHT11.read(DHT11PIN);
  
  printHeader();
  
  switch(chk)
  {
    case DHTLIB_OK:
      printTemperature();
      printHumidity();
      printDewPoint();
      break;
      
    case DHTLIB_ERROR_CHECKSUM:
      lcd.println("# Checksum Error");
      break;
      
    case DHTLIB_ERROR_TIMEOUT:
      lcd.println("# Timeout Error");
      break;
    
    default:
      lcd.println("# Unknown Error");
      break;
  }
  
  lcd.display();
}

void printHeader()
{
  lcd.clearDisplay();
  
  lcd.print(Date);
  lcd.print("   ");
  lcd.print(Time);
  lcd.println("");
  lcd.print("      In   Out"); 
}

void printTemperature()
{
  InTemperature = DHT11.temperature;
  
  lcd.print("Temp: ");
  lcd.print(InTemperature);
  lcd.print("C  ");
  lcd.print(OutTemperature);
  lcd.print("C");
}

void printHumidity()
{
  InHumidity = DHT11.humidity;
  lcd.print("Humi: ");
  lcd.print(InHumidity);
  lcd.print("%  ");
  lcd.print(OutHumidity);
}

void printDewPoint()
{
  double inDewPoint  = dewPoint(DHT11.temperature, DHT11.humidity);
  
  lcd.print("DP ");
  if(inDewPoint < 10)
  {
    lcd.setTextColor(WHITE, BLACK);
  }
  lcd.print(inDewPoint);
  
  lcd.setTextColor(BLACK);
  lcd.print(" ");
  
  lcd.println(OutDewPoint);
  lcd.setTextColor(BLACK);
  
  lcd.display();
}

void getOutTemperature()
{
  lcd.clearDisplay();
  lcd.println("Connecting....");
  lcd.display();
  
  delay(1000);

  if(client.connect(server, 80))
  {
    lcd.println("Connected.");
    lcd.display();
    delay(1000);
    
    sendRequest(client);
  }
  else
  {
    lcd.println("Failed.");
    lcd.display();
    delay(1000);
    return;
  }
  
  extractData(client);
  client.stop();
}

/**
* Send the request to server
*/
void sendRequest(EthernetClient client)
{
  client.print("GET /duino/inout/v3/inout.php?T=");
  client.print(InTemperature);
  client.print("&H=");
  client.print(InHumidity);
  client.print("&P=");
  client.print(InPlacename);
  client.println(" HTTP/1.1");
  client.println("Host: ubuntudev");
  client.println("User-Agent: Arduino");
  client.println("Connection: close");
  client.println("");  
}

void extractData(EthernetClient client)
{
  char currentValue[16];
  boolean dataFlag = false; // True if data has started
  boolean endFlag  = false; // True if data is reached
  int j = 0;
  int i = 0;
  
  while(client.connected() && !endFlag)
  {
    char c = client.read();
    
    Serial.print(c);
    
    if(c == '<')
    {
      dataFlag = true;
      hasStats = true;
      lcd.clearDisplay();
      lcd.println("Extracting...");
      lcd.display();
    }
    else if(dataFlag && c == '>') // end of data
    {      
      setStatValue(j, currentValue);
      endFlag = true;
      lcd.println("!");
      lcd.display();
      delay(2000);
      lcd.clearDisplay();
    }
    else if(dataFlag && c == '|') // next dataset
    {
      setStatValue(j++, currentValue);
      char currentValue[16];
      i = 0;
      lcd.print(".");
      lcd.display();
    }
    else if(dataFlag) // data
    {
      currentValue[i++] = c;
      lcd.print(".");
      lcd.display();
    }
  }
}

/**
* set a simple stat value depending on the position in the string returned
* @param integer position
* @param string value
*/
void setStatValue(int position, char value[])
{
  switch(position)
  {
    case 0:
      for(int i=0 ; i< 6; i++) {
        Date[i] = value[i];
      }
      break;
      
    case 1:
      for(int i=0; i< 5; i++) {
        Time[i] = value[i];
      }
      break;
      
    case 2:
      for(int i=0; i< 16; i++) {
        OutPlacename[i] = value[i];
      }
      break;
      
    case 3:
      for(int i=0; i< 2; i++) {
        OutTemperature[i] = value[i];
      }
      break;
      
    case 4:
      for(int i=0;i< 3; i++) {
        OutHumidity[i] = value[i];
      }
      break;
      
    case 5:
      for(int i=0;i< 3; i++) {
        OutDewPoint[i] = value[i];
      }
      break;
  }
}

// dewPoint function NOAA
// reference: http://wahiduddin.net/calc/density_algorithms.htm 
double dewPoint(double celsius, double humidity)
{
  double RATIO = 373.15 / (273.15 + celsius);  // RATIO was originally named A0, possibly confusing in Arduino context
  double SUM = -7.90298 * (RATIO - 1);
  SUM += 5.02808 * log10(RATIO);
  SUM += -1.3816e-7 * (pow(10, (11.344 * (1 - 1/RATIO ))) - 1) ;
  SUM += 8.1328e-3 * (pow(10, (-3.49149 * (RATIO - 1))) - 1) ;
  SUM += log10(1013.246);
  double VP = pow(10, SUM - 3) * humidity;
  double T = log(VP/0.61078);   // temp var
  return (241.88 * T) / (17.558 - T);
}
