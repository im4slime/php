# CIPHER Shipping Admin

## Install on WAMP
1. Extract the `CIPHER_Shipping_Admin` folder into `C:\wamp64\www\`.
2. Start WAMP and wait until its icon is green.
3. Open `http://localhost/phpmyadmin/`.
4. Select the SQL tab and import/run the contents of `database.sql`. If you already created the same tables, `CREATE TABLE IF NOT EXISTS` will preserve them.
5. Check `config.php`. The default WAMP database settings are host `localhost`, username `root`, and blank password. Change them if you set a MySQL password.
6. Visit `http://localhost/CIPHER_Shipping_Admin/setup_admin.php`.
7. Create an administrator with your own name, username, email, and a password of at least 10 characters.
8. Sign in at `http://localhost/CIPHER_Shipping_Admin/`.
9. After creating the administrator, delete `setup_admin.php` from the website folder.

## Features
- Database-backed administrator login with password hashing
- Dashboard statistics and recent shipments
- Add and list customers
- Add and list shipments
- Add tracking updates and update shipment status
- Basic reports
- Searchable tables and responsive layout

## Notes
- This is a local starter project. Before public deployment, configure HTTPS, strong database credentials, CSRF protection, backups, and production-safe server settings.
- `shipping_cost` is displayed as a numeric amount without a currency symbol because the currency depends on your business.
- If your current folder is `C:\wamp64\www\phpp`, either extract this project there and use `http://localhost/phpp/`, or use the included folder name and URL consistently.
