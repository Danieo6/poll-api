# Poll API
This API was created for a poll page. For now, there's no license. You are prohibited for using any parts of this code (except third-party code, like Lumen Framework)!
Only viewing allowed!

## Features:
* Authorization system with JWT
* Creating polls (ask a question, add answers, set the closing date and check if users can select multiple answers)
* Voting in polls (single answer or multiple, dependent on poll settings)
* Checking poll results
* Multi-vote protection with browser fingerprinting

## Work-in-progress
* ReCAPTCHA integration
* Poll modification (manual poll closing, adding/removing answers)
* Poll comments
* Admin endpoints
* Multi-vote protection based on cookies and IP

**NOTE: There's no documentation currently. For API endpoints just check the routes/api.php file and the controllers**