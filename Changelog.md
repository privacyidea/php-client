### v2.0.0
* Added an optional client parameter to forward the client IP with the server requests (#17)
* Code updated to support PHP8 (#46)
* Tests will be temporarily disabled until modernization of http-mock is complete (#46)
* PrivacyIDEA and PIResponse classes made private (#51)
* Removed old EnrollToken function (#46)

### v1.0.0
* Added a possibility to enroll a new token via challenge (#23)
* Implementation of the preferred client mode (#20)

### v0.9.3

* Supporting following tokens: OTP, Push, WebAuthn, U2F
* Token enrollment in the application
* Multiple WebAuthn

### v0.9.0
* First Version supporting /validate/check and /validate/triggerchallenge endpoints
