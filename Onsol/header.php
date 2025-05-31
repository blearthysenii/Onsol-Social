<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header>
  <div class="header-container">
    <h1>Onsol</h1>

    <nav>
      <a href="home.php" title="Home" class="<?= $current_page === 'home.php' ? 'active' : '' ?>">
          <i class="fa fa-home"></i>
      </a>
      <a href="upload.php" title="Upload" class="<?= $current_page === 'upload.php' ? 'active' : '' ?>">
          <i class="fa fa-upload"></i>
      </a>
      <a href="saved.php" title="Saved" class="<?= $current_page === 'saved.php' ? 'active' : '' ?>">
          <i class="fa fa-bookmark"></i>
      </a>
      <a href="friend_requests.php" title="Frends" class="<?= $current_page === 'friend_requests.php' ? 'active' : '' ?>">
          <i class="fa fa-user-friends"></i>
      </a>
      <a href="messages.php" title="Messages" class="<?= $current_page === 'messages.php' ? 'active' : '' ?>">
  <i class="fa fa-envelope"></i>
</a>


      <a href="profile.php" title="Profile" class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">
          <i class="fa fa-user"></i>
      </a>
      
    </nav>

    <form action="search.php" method="get" class="search-form">
      <input type="text" name="query" placeholder="Search users..." required>
      <button type="submit"><i class="fa fa-search"></i></button>
    </form>
  </div>
</header>

<style>

   *,
  *::before,
  *::after {
    box-sizing: border-box;
  }
  html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  box-sizing: border-box;
}
header {
  position: sticky;
  top: 0;
  width: 100%;
  height: 70px;
  padding: 4px 20px;
  background: rgba(0, 30, 100, 0.95);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 20px rgba(120, 0, 180, 0.25);
  z-index: 1000;
  border-radius: 0 0 16px 16px;
}

.header-container {
  display: flex;
  align-items: center;
  height: 100%;
  gap: 40px;
  justify-content: space-between; /* kjo vendos elementet ne skajet e containerit */
}

.header-container h1 {
  margin-left: 20px;
  font-size: 28px;
  color: white;
  background: linear-gradient(135deg, #348aff, #ff5ef7);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0;
  flex-shrink: 0; /* mos lejo që titulli të zvogëlohet */
}

/* Për të vendosur ikonat ne mes, do përdorim flex me auto margins */

.header-container nav {
  display: flex;
  gap: 30px;
  justify-content: center;
  flex-grow: 1; /* ikona zgjerohet në mes */
}

nav{
  margin-left: 72px;
}
nav a {
  margin-left: 50px;
  font-size: 24px;
  color: #ffffffbb;
  text-decoration: none;
  padding: 10px 14px;
  border-radius: 12px;
  transition: all 0.3s ease-in-out;
  position: relative;
}

nav a:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 16px rgba(120, 0, 180, 0.5);
  transform: translateY(-2px);
}

nav a.active {
  color: #fff;
  font-weight: bold;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
  padding-bottom: 5px;
}

.search-form {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-shrink: 0; 
}

.search-form input[type="text"] {
  padding: 6px 12px;
  border-radius: 12px;
  border: none;
  outline: none;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  font-size: 16px;
  width: 180px;
  height: 36px; 
  box-sizing: border-box;
   backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2); /* kufi i lehtë si te xhami */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); /* pak hije për thellësi */
}



.search-form input::placeholder {
  color: #ddd;
}

.search-form button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0; /* ose padding i vogël, për të mos shtyrë ikonën */
  background: transparent;
  border: none;
  color: #fff;
  font-size: 20px;
  cursor: pointer;
  transition: color 0.3s;
  height: 100%;
  
}

.search-form button:hover {
  color: #ff0;
}
.search-form button i.fa-search {
  line-height: 1;
}

</style>
