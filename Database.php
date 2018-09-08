<?php



$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bot";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
                                    // Check connection
  if (!$conn) {
 die("Connection failed: " . mysqli_connect_error());
                                    }

//  $sql = "INSERT INTO anggota (chat_id,nama,telepon,email) VALUES ('{$_SESSION['chat_id']}','{$_SESSION['nama']}' ,'{$_SESSION['telepon']}', '{$_SESSION['email']}')";


//  if (mysqli_query($conn, $sql)) {

//     $hasil =  "data berhasil di masukan, silahkan melakukan /login";
// }
//  else {
//  $hasil =  "Error... ";
// }