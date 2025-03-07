# SimpleBase JavaScript Library

SimpleBase is a lightweight JavaScript library designed to simplify interactions with the SimpleBase backend, which utilizes PHP and SQLite. This library provides a straightforward API for creating databases, managing data, and sharing access through a minimal user interface.

## Features

- **Token Management**: Automatically generates and manages random tokens for secure access to the backend.
- **Database Operations**: Easily create tables, write data, retrieve data, and update records in your SimpleBase database.
- **User Interface**: Includes a minimal UI for sharing URLs and entering tokens, enhancing user experience.
- **Local Storage**: Automatically saves tokens to local storage for persistent access.
- **Validation**: Ensures that user inputs are valid before making requests to the backend.

## Installation

To use the SimpleBase library, include the `simplebase.js` or `simplebase.min.js` file in your project:

```html
<script src="path/to/simplebase.min.js"></script>
```

Alternatively, you can install it via npm:

```bash
npm install simplebase-js
```

## Usage

### Generating a Token

To generate a random token, use the `createToken` function:

```javascript
import { createToken } from 'simplebase-js/src/core/token';

const token = createToken();
console.log(token);
```

### Creating a Table

To create a new table in your database:

```javascript
import { createTable } from 'simplebase-js/src/core/database';

createTable('users', [
    { cname: 'name', type: 'VARCHAR' },
    { cname: 'age', type: 'INTEGER' }
]);
```

### Writing Data

To add a new row to a table:

```javascript
import { writeData } from 'simplebase-js/src/core/database';

writeData('users', [
    { cname: 'name', value: 'John' },
    { cname: 'age', value: 30 }
]);
```

### Retrieving Data

To get all data from a table:

```javascript
import { getData } from 'simplebase-js/src/core/database';

getData('users');
```

### Updating Data

To update a specific record in a table:

```javascript
import { updateData } from 'simplebase-js/src/core/database';

updateData('users', 'name="John"', { cname: 'age', value: 31 });
```

### Sharing the URL

To display a minimal UI for sharing the URL with the token:

```javascript
import { displayShareUI } from 'simplebase-js/src/ui/share';

displayShareUI();
```

## API Reference

For detailed information on all available functions and their parameters, please refer to the source code in the `src` directory.

## Contributing

Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License

This project is licensed under the MIT License. See the LICENSE file for more details.