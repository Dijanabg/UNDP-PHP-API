<form method="post" action="register.php">
  <label for="username">Username:</label>
  <input type="text" name="username" id="username" required>
  <br>
  <label for="password">Password:</label>
  <input type="password" name="password" id="password" required>
  <br>
  <label for="account_type">Account Type:</label>
  <input type="radio" name="account_type" value="admin" id="account_type_admin">
  <label for="account_type_admin">Admin</label>
  <input type="radio" name="account_type" value="user" id="account_type_user" checked>
  <label for="account_type_user">User</label>
  <br>
  <input type="submit" value="Register">
</form>
