# CIPHER Customer Package Tracking Page

This adds a public customer tracking page connected to the existing CIPHER MySQL database.

## Install
1. Extract the `CIPHER_Customer_Tracking` folder.
2. Copy `track.php` into your existing website folder, for example `C:\wamp64\www\phpp\track.php`.
3. Copy `assets\css\tracking.css` into your existing website's `assets\css\tracking.css`. Create the folders if needed.
4. Confirm your existing `config.php` connects to `cipher_db` using PDO and does not call `requireLogin()` automatically.
5. Open `http://localhost/phpp/track.php` (change `phpp` if your actual project folder has a different name).

## How it works
- Customers enter their tracking number.
- The page finds the shipment in `shipments`.
- It shows origin, destination, status, ship date, estimated delivery, and the tracking history recorded in `tracking_events`.
- Staff must add updates in the admin Tracking page for new timeline entries to appear.

## Important
This page is intended to be public, so `track.php` does not require an admin login. Do not add `requireLogin()` to this page.
For public deployment, use HTTPS, keep database credentials private, and consider limiting public details shown by tracking number.
