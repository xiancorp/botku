<?php

// $username = $_POST['username'];
// $password = md5($_POST['password']);
 
// $login = mysql_query("select * from user where username='$username' and password='$password'");
// $cek = mysql_num_rows($login);
 
// if($cek > 0){
// 	session_start();
// 	$_SESSION['username'] = $username;
// 	$_SESSION['status'] = "login";
// 	$hasil="terimakasih, anda telah login dengan id chat :$chatid";
// }else{
// 	$hasil="anda gagal login";	
// }





include ('Database.php');


session_start();
   
   $user_check = $_SESSION['username'];
   
   $ses_sql = mysqli_query($conn,"select username from anggota where username = '$user_check' ");
   
   $row = mysqli_fetch_array($ses_sql,MYSQLI_ASSOC);
   
   $login_session = $row['username'];
   
   if(!isset($_SESSION['login_user'])){
      $hasil="anda berhasil login");
   }