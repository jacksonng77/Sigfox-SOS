#include "SIGFOX.h"

static const String device = "g88pi";                                 //  Set this to your device name if you're using UnaBiz Emulator.
static const bool useEmulator = false;                                //  Set to true if using UnaBiz Emulator.
static const bool echo = true;                                        //  Set to true if the SIGFOX library should display the executed commands.
static const Country country = COUNTRY_SG;                            //  Set this to your country to configure the SIGFOX transmission frequencies.
static UnaShieldV2S transceiver(country, useEmulator, device, echo);  //  Uncomment this for UnaBiz UnaShield V2S Dev Kit

int BUTTON1 = 13; //button is hooked up to D13 pin

void setup(){

Serial.begin(9600);
  pinMode(BUTTON1,INPUT);

  Serial.println(F("Running setup..."));  
  if (!transceiver.begin()) stop(F("Unable to init SIGFOX module, may be missing"));  //  Will never return.

  transceiver.sendMessage("0102030405060708090a0b0c");
  Serial.println("Waiting 10 seconds...");
  delay(10000);
}

void loop(){
  Serial.println( );

  if(digitalRead(BUTTON1) == HIGH) //button is pressed
  { 
    Serial.println("Button1 1"); 
    Message msg(transceiver);     //prepare message to send 
    msg.addField("d1", "zoe");    //here are all the 12 bytes you can send at once
    msg.addField("d2", "ned");    //as an example, I maxed out all 12 bytes. each field is 4 bytes, "d1" is the key, "zoe" is the data
    msg.addField("d3", "hep");    //you really need to send just 4 bytes to say that a button is pressed
    msg.send();                   //send it!
  } 

  delay(200);
}
