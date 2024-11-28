# 2048 Game

A modern implementation of the classic 2048 game with enhanced features and multiplayer capabilities.

## Features

- Classic 2048 gameplay
- User authentication system
- Save/Load game states
- Undo moves
- Daily challenges
- Achievement system
- Mobile touch support
- Sound effects
- Points and leaderboard system
- Multiple board sizes
- Dark mode support

## Technologies Used

- PHP (MVC Architecture)
- MySQL
- JavaScript (Vanilla)
- HTML5
- CSS3
- Bootstrap

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/2048.git
```

2. Import the database schema:
```bash
mysql -u your_username -p your_database_name < database.sql
```

3. Configure your database connection:
   - Copy `app/config/config.example.php` to `app/config/config.php`
   - Update the database credentials in `config.php`

4. Set up your web server:
   - Point your web server to the `public` directory
   - Ensure URL rewriting is enabled for your web server

5. Install dependencies:
```bash
composer install
```

## Project Structure

```
2048/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── libraries/
│   ├── models/
│   └── views/
├── public/
│   ├── css/
│   ├── js/
│   ├── sounds/
│   └── index.php
└── database.sql
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Original 2048 game by Gabriele Cirulli
- All contributors who have helped with features and bug fixes
