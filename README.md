# Database Management

**Contributors:** worais \
**Tags:** wordpress, login, waf, ddos \
**Requires at least:** 5.6 \
**Tested up to:** 6.5 \
**Requires PHP:** 7.2 \
**License:** GPLv3 or later \
**Stable tag:** 1.1.0

Worais Database Management plugin Allows you to easily manage your WordPress database :)

## Features

- **SQL Query Execution**: Execute any SQL query directly from the WordPress dashboard. Type your SQL statements and see the results immediately.
- **Advanced Filters**: Apply filters to refine your queries and find exactly what you need, quickly and efficiently.
- **Record Editing**: Edit records directly through the plugin interface, without the need to manually access the database.
- **Record Deletion**: Delete unwanted records easily and securely with just a few clicks.
- **Pagination**: Navigate through your query results with integrated pagination, making it easier to view and manage large volumes of data.
- **Intuitive Interface**: User-friendly and easy-to-use interface designed to facilitate database management even for users with little technical experience.
- **Security**: Implements security measures to protect your queries and sensitive data, ensuring that only authorized users can make changes to the database.

## Installation

1. Install this plugin using WordPress' built-in installer
2. Access the **Database Management** option under **Settings**
3. Follow the instructions to set up and configure

## Support

If you encounter issues or have improvement suggestions, please [open an issue](https://github.com/worais/database-management/issues).

## Contributions

Contributions are welcome! Feel free to fork the project and submit [pull requests](https://github.com/worais/database-management/pulls).

## Screenshots
![](https://github.com/worais/database-management/blob/main/screenshots/1.png?raw=true)
![](https://github.com/worais/database-management/blob/main/screenshots/2.png?raw=true)
![](https://github.com/worais/database-management/blob/main/screenshots/3.png?raw=true)

## Running Tests

To execute the unit tests for Login Protect, you can use Docker Compose. Make sure you have Docker and Docker Compose installed on your system.

1. Open a terminal and navigate to the root directory of the repository.

2. Run the following command to start the tests:

   ```bash
   docker-compose run wordpress php vendor/bin/phpunit
   ```