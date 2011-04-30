<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  class user {

    //-------------------------------------------------------------------
    // 1) Direct according to path argument 1
    //-------------------------------------------------------------------
    function menu() {
        switch ($GLOBALS['args'][1]){
            case "login":
                return $this->login();
                break;
            case "register":
                return $this->register();
                break;
            case "confirm":
                return $this->confirm();
                break;
            default:

        }
    }

    //-------------------------------------------------------------------
    // Page: Login
    //-------------------------------------------------------------------    
    function login()
    {
      $variables['title'] = "Login";
      $out = '

      <form name="login" action="'.$GLOBALS['systempath'].'home" method="post">
        <label>Username:</label> <input type="text" name="username" /><br/ ><br/ >
        <label>Password:</label> <input type="password" name="password" />
       <div><input type="submit" value="Login" />  </div>
      </form>';
 
      $variables['content'] = $out;
      return $variables;
    }

    //-------------------------------------------------------------------
    // Page: Register
    //-------------------------------------------------------------------    
    function register()
    {
      $variables['title'] = "Register";
      $out = '

      <form name="login" action="'.$GLOBALS['systempath'].'user/confirm" method="post">
        <label>Username:</label> <input type="text" name="username" /><br/ ><br/ >
        <label>Password:</label> <input type="password" name="pass1" /><br/ ><br/ >
        <label>once again:</label> <input type="password" name="pass2" /><br/ ><br/ >
        <div><input type="submit" value="register" /></div>
      </form>';
 
      $variables['content'] = $out;
      return $variables;
    }

    //-------------------------------------------------------------------
    // Registration confirmation page
    //-------------------------------------------------------------------  
    function confirm()
    {
      $variables['title'] = "Confirmation";
      
      $username = $_POST['username'];
      $pass1 = $_POST['pass1'];
      $pass2 = $_POST['pass2'];

      $out = '';
      if($pass1 != $pass2) $out .= "Passwords dont match";
      if(strlen($username) > 30) $out .= "Username too long";

      $hash = hash('sha256', $pass1);


      $string = md5(uniqid(rand(), true));
      $salt = substr($string, 0, 3);

      $hash = hash('sha256', $salt . $hash);

      $query = "INSERT INTO users ( username, password, salt )
        VALUES ( '$username' , '$hash' , '$salt' );";

      db_query($query);

      $out .= "You are now registered";
      $variables['content'] = $out;
      return $variables;

    }

    //-------------------------------------------------------------------
    // User box element
    //-------------------------------------------------------------------  
    function userbox()
    {
      $out="";

     // if (isset($_SESSION['valid']))
     // {

      if (!$_SESSION['valid'])  
      { 
        $out = '
        <form name="login" action="'.$GLOBALS['systempath'].'home" method="post">
          <label>Username:</label> <input type="text" name="username" /><br/ >
          <label>Password:</label> <input type="password" name="password" />
          <div style="margin-left:70px;"><input type="submit" value="Login" /> or <a href="'.$GLOBALS['systempath'].'user/register">register</a> </div>
        </form>';
      }

      if ($_SESSION['valid'])   $out = '
      <form name="logout" action="'.$GLOBALS['systempath'].'home" method="post">
        <input type="hidden" name="logout"/>
        <label class="loginLabel" >Welcome!</label> <input type="submit" value="Logout" style="float:right;"/>
      </form>';
      //}

      return $out;
    }

  function handler()
  {  
    //-------------------------------------------------------------------------------
    // If username and password are included in POST then check if valid and logon
    //-------------------------------------------------------------------------------
    if (isset($_POST['username']) && isset($_POST['password']))
    {
      $username = $_POST['username'];
      $password = $_POST['password'];
     // $username = $db->real_escape_string($username);

      $result = db_query("SELECT id,password, salt FROM users WHERE username = '$username'");


      if(mysql_num_rows($result) < 1) $_SESSION['valid'] = 0; //no such user exists

      $userData = mysql_fetch_array($result);
      $hash = hash('sha256', $userData['salt'] . hash('sha256', $password) );
      if($hash != $userData['password'])
      {
        $_SESSION['valid'] = 0; //incorrect password
      }
      else
      {
        //this is a security measure
        //session_regenerate_id (); 
        $_SESSION['valid'] = 1;
        $_SESSION['userid'] = $userData['id'];
      }

    }
    //-------------------------------------------------------------------------------

    //-------------------------------------------------------------------------------
    // Handle logout
    //-------------------------------------------------------------------------------
    if (isset($_POST['logout']))
    {
      $_SESSION['valid'] = 0;
      session_destroy();
    }
  }
}

?>
