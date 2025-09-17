Nice, you’ve already drafted a solid `README.md` for **omniSEO** 👌
Based on all the details you’ve shared with me about **omniSEO** (your SaaS project, features, future enhancements, tech stack, GitHub setup, and roadmap), here’s a **completed, polished version** of your project documentation:

---

# omniSEO - AI-Powered SEO Content Generator

A **subscription-based SaaS platform** that helps businesses, marketers, and bloggers generate **SEO-optimized content** using advanced AI models.

---

## 🚀 Features

* 🤖 **AI-powered content generation** using OpenAI API (GPT-3.5 / GPT-4 / GPT-4.1-mini options)
* 📊 **SEO-optimized articles** with headings, subheadings, and structured formatting
* 💳 **Credit-based system** (10 credits per generation, configurable)
* 📚 **Content history** with view, download, and delete options
* 👤 **User authentication** (signup, login, logout, session management)
* 📱 **Responsive UI** (mobile-friendly dashboard and editor)
* 🔒 **Secure backend** (prepared statements, password hashing, CSRF-ready)
* 🌐 **WordPress integration (planned)** – publish directly to your blog
* 🕒 **Ad loading cooldown system** (for the Android app version, prevents overloading AdMob)

---

## 🛠 Tech Stack

* **Frontend**: HTML5, CSS3, Vanilla JavaScript
* **Backend**: PHP 8+ with MySQLi
* **Database**: MySQL
* **API**: OpenAI GPT-4.1-mini (via API)
* **Security**: Password hashing (`password_hash`), prepared statements, input sanitization, session-based auth

---

## 📂 Project Structure

```
omniSEO/
├── index.php               # Landing page (root)
├── login/                  # Login module
│   └── index.php
├── signup/                 # Registration module
│   └── index.php
├── dashboard/              # Main dashboard
│   └── index.php
├── history/                # Content history
│   └── index.php
├── account/                # Account settings
│   └── index.php
├── article/                # Article viewer
│   └── index.php
├── api/                    # API endpoints
│   ├── generate.php        # Content generation
│   ├── logout.php          # User logout
│   ├── download.php        # Content download
│   ├── delete.php          # Delete content
│   ├── publish.php         # WordPress publishing (placeholder)
│   └── buy-credits.php     # Payment processing (placeholder)
├── assets/                 # Static assets
│   ├── css/style.css       # Main stylesheet
│   ├── js/main.js          # JavaScript functionality 
    └── images              # Image upload folder
├── includes/               # PHP includes
│   ├── config.php          # Configuration
│   ├── db.php              # Database connection
│   └── auth.php            # Authentication class
├── sql/                    # Database schema
│   └── schema.sql          # Database structure
├── .env                    # Environment variables
└── README.md               # Project documentation
             # Project documentation
```

---

## ⚙️ Installation

1. **Clone the repository**

   ```bash
   git clone git@github.com:nazmusM/omniseo.git
   cd omniseo
   ```

2. **Set up the database**

   * Create a MySQL database
   * Import `sql/schema.sql`

3. **Configure environment variables**

   * Copy `.env.example` to `.env`
   * Update database credentials and API key

   ```env
   DB_HOST=localhost
   DB_NAME=omniseo_db
   DB_USER=your_db_username
   DB_PASS=your_db_password
   OPENAI_API_URL=your_openai_api_url_here
   OPENAI_API_KEY=your_openai_api_key_here
   SITE_URL=http://localhost/omniSEO
   SITE_NAME=omniSEO
   ```

4. **Set up web server**

   * Ensure PHP 8+ is installed with MySQLi extension

5. **Test installation**

   * Visit your domain
   * Register an account and try generating content

---

## 🧑‍💻 Usage

1. **Register**: Create a free account (100 free credits)
2. **Login**: Secure session-based login
3. **Generate Content**:

   * Enter topic/prompt
   * Choose content type, tone, length and other settings
   * Generate SEO-ready article (credit cost varies)
4. **Manage Content**:

   * View in history
   * Download in `.txt` or `.docx`
   * Delete if no longer needed
5. **Account Management**:

   * Track credit balance
   * Update account info
   * Purchase more credits (coming soon)

---

## 🔐 Security Features

* Password hashing with `password_hash()`
* MySQLi prepared statements prevent SQL injection
* Input sanitization & validation
* Session-based authentication
* CSRF protection (ready to implement)
* Environment variables keep secrets secure

---

## 💳 Credits System

* **100 free credits** on signup
* ** credit cost varies per generation** (default, configurable)
* Larger word count = higher credit cost (future enhancement)
* Balance shown in dashboard

---

## 🗺 Roadmap / Future Enhancements

* [ ] **Stripe integration** for purchasing credits
* [ ] **WordPress plugin** for one-click publishing
* [ ] **Advanced AI models** (GPT-4.1-mini, fine-tuned SEO models)
* [ ] **Team collaboration** (shared accounts & roles)
* [ ] **Content scheduling & auto-publishing**
* [ ] **SEO analytics dashboard** (keywords, ranking predictions)

---

## 🔗 API Endpoints

* `POST /api/autowriter.php` – Generate AI content
* `GET /api/download.php?id={id}` – Download article
* `POST /api/delete.php` – Delete generated content
* `POST /api/publish.php` – Publish to WordPress (future)
* `POST /api/buy-credits.php` – Payment integration (future)

---

## 📜 License

This project is **proprietary software**.
All rights reserved.

---

## 📧 Support

For support, please contact:
**Nazmus Sakib** – \[[nazmussakibsyam2@gmail.com](mailto:nazmussakibsyam2@gmail.com)]

---


