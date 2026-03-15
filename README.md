# My Notes & Tasks 📝✅

A modern, responsive web application that combines a To-Do list and a Notepad into one seamless experience. 

## 🌟 Key Features
* **Hybrid Storage System:**
  * **Guest Mode:** Users can write notes and tasks without an account. Data is saved temporarily in the browser using JavaScript `localStorage`.
  * **Logged-In Mode:** Users who create an account get permanent "Cloud Sync." Their data is securely stored in a MySQL database and accessible from any device.
* **Secure Authentication:** User registration and login system featuring hashed passwords and protected routes.
* **Full CRUD Functionality:** Create, Read, Update, and Delete both tasks and notes.
* **Profile Dashboard:** A user dashboard that tracks total tasks and notes, includes account deletion (with cascading database cleanup), and secure password updates.
* **Responsive UI:** Built with a mobile-first approach, ensuring the app looks great on desktop and mobile.

## 🛠️ Tech Stack
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla ES6), Bootstrap 5.3, FontAwesome 6.
* **Backend:** PHP 8.
* **Database:** MySQL (Relational database with Foreign Keys and `ON DELETE CASCADE` setup).

## 🚀 Setup & Installation (Local Development)
1. Clone this repository to your local XAMPP/WAMP `htdocs` directory.
2. Open your local `phpMyAdmin` and create a database (e.g., `todo_db`).
3. Run the SQL commands (provided in the project notes) to generate the `users`, `tasks`, and `notepad` tables.
4. Update `config/db.php` with your local database credentials.
5. Open the project in your browser at `http://localhost/todo-list`.