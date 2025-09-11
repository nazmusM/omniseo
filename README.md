# omniSEO - AI-Powered SEO Content Generator

A subscription-based SaaS platform for generating SEO-optimized content using AI technology.

## Features

- 🤖 AI-powered content generation using OpenAI API
- 📊 SEO-optimized articles with proper structure
- 💳 Credit-based system (10 credits per generation)
- 📚 Content history and management
- 👤 User authentication and account management
- 📱 Responsive design
- 🔒 Secure with prepared statements and password hashing

## Tech Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8 with MySQLi
- **Database**: MySQL
- **API**: OpenAI GPT-3.5-turbo
- **Security**: Password hashing, prepared statements, session management

## Installation

1. **Clone the repository**
   \`\`\`bash
   git clone <repository-url>
   cd omniSEO
   \`\`\`

2. **Set up the database**
   - Create a MySQL database
   - Import the schema from `sql/schema.sql`

3. **Configure environment variables**
   - Copy `.env.example` to `.env`
   - Update database credentials
   - Add your OpenAI API key

4. **Set up web server**
   - Point document root to the `public` folder
   - Ensure PHP 8+ is installed with MySQLi extension

5. **Test the installation**
   - Visit your domain
   - Create an account and test content generation

## Environment Variables

\`\`\`env
DB_HOST=localhost
DB_NAME=omniseo_db
DB_USER=your_db_username
DB_PASS=your_db_password
OPENAI_API_KEY=your_openai_api_key_here
SITE_URL=http://localhost/omniSEO
SITE_NAME=omniSEO
\`\`\`

## Project Structure

\`\`\`
omniSEO/
├── public/                 # Web-accessible files
│   ├── index.php          # Landing page
│   ├── login.php          # Login page
│   ├── signup.php         # Registration page
│   ├── dashboard.php      # Main dashboard
│   ├── history.php        # Content history
│   ├── account.php        # Account settings
│   └── view-article.php   # Article viewer
├── api/                   # API endpoints
│   ├── generate.php       # Content generation
│   ├── logout.php         # User logout
│   ├── download.php       # Content download
│   ├── delete.php         # Delete content
│   ├── publish.php        # WordPress publishing (placeholder)
│   └── buy-credits.php    # Payment processing (placeholder)
├── assets/                # Static assets
│   ├── css/style.css      # Main stylesheet
│   └── js/main.js         # JavaScript functionality
├── includes/              # PHP includes
│   ├── config.php         # Configuration
│   ├── db.php             # Database connection
│   └── auth.php           # Authentication class
├── sql/                   # Database schema
│   └── schema.sql         # Database structure
└── .env                   # Environment variables
\`\`\`

## Usage

1. **Registration**: Users sign up with email and password
2. **Login**: Secure authentication with session management
3. **Generate Content**: 
   - Enter topic/prompt
   - Select content type, tone, and length
   - Generate SEO-optimized content (costs 10 credits)
4. **Manage Content**: View, download, or delete generated content
5. **Account Management**: Monitor credits and account settings

## Security Features

- Password hashing with `password_hash()`
- MySQLi prepared statements prevent SQL injection
- Input sanitization and validation
- Session-based authentication
- CSRF protection ready
- Environment variable configuration

## Future Enhancements

- [ ] Stripe/PayPal payment integration
- [ ] WordPress publishing plugin
- [ ] Advanced AI models (GPT-4)
- [ ] Team collaboration features
- [ ] API access for developers
- [ ] Content scheduling
- [ ] SEO analytics dashboard

## API Endpoints

- `POST /api/generate.php` - Generate content
- `GET /api/download.php?id={id}` - Download content
- `POST /api/delete.php` - Delete content
- `POST /api/publish.php` - Publish to WordPress (placeholder)
- `POST /api/buy-credits.php` - Purchase credits (placeholder)

## Credits System

- New users start with 100 free credits
- Each content generation costs 10 credits
- Word count affects credit usage
- Credit balance displayed throughout the app

## License

This project is proprietary software. All rights reserved.

## Support

For support, please contact [your-email@domain.com]
