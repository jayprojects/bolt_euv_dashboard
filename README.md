# Communicating with GM cars via onstar

![bolt_euv_dashboard](/bolt_euv.jpg "Bolt EUV Dashboard")

## History

I created this project to communicate with my Bolt EUV 2023. I could retrieve data in real-time, such as Charge level, state, range, temp. As well as execute command like lock unlock cars on/off alarm. 
And most importantly the whole reason i implemented this to start stop charging. A the time of my investigation the onstart mobile app does not do it. 
I didn't find a way do it on the api side either but i found a work around
Basically you can set charge mode peak/off peak by the api. Basically i set very small amount of time as off-peak and rest as peak, so by setting off-peak i could effectively stop charging!

I really enjoyed working on this project. I ended up not using it much because #1 it require some effort to maintain the api's client_id and device_id and #2 the companion system (solar charger) that supposed to control(use) this on/off charging, i didn't get time to implement! I just let the charger to go in fault mode when not enough power at the solar panel (not related this project). 

The EV dashboard worked just fine. 

As best of my understanding gm/onstar does not authorize to use this apis. So i would suggest to keep it at personal learning purpose only. and do everything at your own risk as usual!

## License

The MIT License (MIT)



