#Onsol


Onsol is a unique public photo-sharing platform where users can upload and view photos in a real-time, community-driven feed. It fosters creativity and social interaction in a fully public space while also offering private messaging between friends.

##🔑 Key Features
📸 Public Photo Feed (index.php)
All photos posted by users are shown on the homepage.
Users can comment on any photo publicly.

##📤 Photo Upload (upload.php)
Upload photos with optional captions to the public feed.

##👥 User System (register.php, login.php, logout.php)
Register, log in, and log out securely.

##💬 Comments on Posts (index.php)
Each photo has a section for public comments — visible to everyone.

##✉️ Private Messages Between Friends (messages.php)
A separate section dedicated to private conversations between connected users (e.g. friends or followers).

#📁 Project Structure

/onsol
├── index.php           # Home page with photo feed and public comments
├── upload.php          # Upload photos with captions
├── messages.php        # Private chat system for friends
├── login.php           # Login page
├── register.php        # Sign-up page
├── logout.php          # Logout script
├── db.php              # Database connection logic
├── css/
│   └── style.css       # Styling for all pages
├── uploads/            # Directory where user-uploaded images are stored

##💡 Why Use Onsol?

Onsol is built to make photo sharing fully public, immediate, and engaging — without the noise of complex algorithms or hidden content. With a balance of public expression (through comments) and private connection (via friend messages), Onsol offers the best of both worlds in a simple and effective platform.