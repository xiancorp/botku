<?php

// session diperlukan untuk simpan step
session_start();

$TOKEN      = "676920704:AAG0vWRnsxys5n5V9418ZUk6kENtfZ3yXsI";

// aktifkan ini jika lagi debugging
$debug = false;
 

// fungsi untuk mengirim/meminta/memerintahkan sesuatu ke bot 
function request_url($method){
    global $TOKEN;
    return "https://api.telegram.org/bot" . $TOKEN . "/". $method;
}
 
// fungsi untuk meminta pesan 
// bagian ebook di sesi Meminta Pesan, polling: getUpdates
function get_updates($offset){
    $url = request_url("getUpdates")."?offset=".$offset;
        $resp = file_get_contents($url);
        $result = json_decode($resp, true);
        if ($result["ok"]==1)
            return $result["result"];
        return array();
}


// fungsi untuk mebalas pesan, 
function send_reply($chatid, $msgid, $text){
    global $debug;
    $data = array(
        'chat_id' => $chatid,
        'text'  => $text,
        'reply_to_message_id' => $msgid   // <---- biar ada reply nya balasannya, opsional, bisa dihapus baris ini
    );
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $context  = stream_context_create($options); 
    $result = file_get_contents(request_url('sendMessage'), false, $context);

    if ($debug) 
        print_r($result);
}

 
// fungsi mengolahan pesan, menyiapkan pesan untuk dikirimkan
function create_response($text, $message)
{
    global $usernamebot;
    // inisiasi variable hasil yang mana merupakan hasil olahan pesan
    $hasil = '';  

    $fromid = $message["from"]["id"]; // variable penampung id user
    $chatid = $message["chat"]["id"]; // variable penampung id chat
    $pesanid= $message['message_id']; // variable penampung id message


    // variable penampung username nya user
    isset($message["from"]["username"])
        ? $chatuser = $message["from"]["username"]
        : $chatuser = '';
    

    // variable penampung nama user

    isset($message["from"]["last_name"]) 
        ? $namakedua = $message["from"]["last_name"] 
        : $namakedua = '';   
    $namauser = $message["from"]["first_name"]. ' ' .$namakedua;

    // ini saya pergunakan untuk menghapus kelebihan pesan spasi yang dikirim ke bot.
    $textur = preg_replace('/\s\s+/', ' ', $text); 

    // memecah pesan dalam 2 blok array, kita ambil yang array pertama saja
    $command = explode(' ',$textur,2); //

	// identifikasi perintah (yakni kata pertama, atau array pertamanya)
    switch ($command[0]) {

		case '/start':
            $hasil = "hai..........`$namauser`, selamat datang, saya inventori_PMS bot,\nuntuk mengakses menu silahkan login: /login.\njika belum memiliki akun silahkan daftar : /daftar.\nkemudian untuk bantuan silahkan klik help : /help";
            break;


        case '/daftar':
            $hasil = "Ok! Sekarang tuliskan nama lengkapmu!";
            $_SESSION['chat_id'] = $chatid;
            $_SESSION['step'] = 'nama';
            break;

        // balasan default jika pesan tidak di definisikan
        default:
			// if(isset($_SESSION['chatid'])){
				if(isset($_SESSION['step'])){
					switch($_SESSION['step']){
						case 'nama':
							//tampung dulu namanya ke SESSION
							$_SESSION['nama'] = $text;
							$hasil = "Ok, {$text}.\nKirimkan password anda!";
							$_SESSION['chat_id'] = $chatid;
							$_SESSION['step'] = 'password';
							break;
						case 'password':
							$_SESSION['password'] = $text;
							$hasil = "Terima kasih,\nsekarang kirimkan alamat emailmu!";
							$_SESSION['chat_id'] = $chatid;
							$_SESSION['step'] = 'email';
							break;
						case 'email':
							$_SESSION['email'] = $text;
							$hasil = "Nama:\t {$_SESSION['nama']},\npsw:\t{$_SESSION['password']},\nemail:\t {$_SESSION['email']}.\nApakah sudah benar? (ya/tidak)";
							$_SESSION['chat_id'] = $chatid;
							$_SESSION['step'] = 'verifikasi';
							break;
						case 'verifikasi':
							if($text == 'ya' || 'Ya'){
								$hasil = "Terima kasih sudah melakuakn registrasi";
								$_SESSION['step'] = 'selesai';
							} else {
								$hasil = "Silahkan ulangi dengan perintah /daftar";
								session_destroy();
							}
							
							break;
						case 'selesai':

                                //connection
                        include("Database.php"); 

                         $sql = "INSERT INTO anggota (chat_id,nama,password,email) VALUES ('{$_SESSION['chat_id']}','{$_SESSION['nama']}' ,'{$_SESSION['password']}', '{$_SESSION['email']}')";


                                if (mysqli_query($conn, $sql)) {

                                    $hasil =  "data berhasil di masukan, silahkan melakukan /login";
                                }
                                 else {
                                 $hasil =  "Error... anda gagal melakuakan pendaftaran, ulangi : /daftar ";
                                 session_destroy();
                                } 

							session_destroy();
							break;


                            //end connection
						
				}
					
					
				} else {
					$hasil = "> Hi, Saya tidak tau maksut anda,\n> silahkan klik help untuk bantuan : /help";
				}
			// }
            break;


        case '/login':
            $hasil = "Ok! Sekarang tuliskan usernamemu!";
            $_SESSION['chat_id'] = $chatid;
            $_SESSION['step'] = 'username';
            break;

        // balasan default jika pesan tidak di definisikan
            default:
                // if(isset($_SESSION['chatid'])){
                    if(isset($_SESSION['step'])){
                        switch($_SESSION['step']){
                            case 'username':
                                //tampung dulu namanya ke SESSION
                                $_SESSION['nama'] = $text;
                                $hasil = "Ok.\nKirimkan password anda!";
                                $_SESSION['chat_id'] = $chatid;
                                $_SESSION['step'] = 'password';
                                break;
                            case 'password':
                                $_SESSION['password'] = $text;
                                $hasil = "Nama:{$_SESSION['nama']}.\npsw:{$_SESSION['password']}.\nApakah sudah benar? (ya/tidak).
                                        ";
                                $_SESSION['chat_id'] = $chatid;
                                $_SESSION['step'] = 'verifikasi';
                                break;
                            case 'verifikasi':
                                if($text == 'ya' || 'Ya'){
                                    $hasil = "Terima kasih, silahkan ketik 'login'";
                                    $_SESSION['step'] = 'login';
                                } else {
                                    $hasil = "Silahkan ulangi dengan perintah /login";
                                    session_destroy();
                                }
                                
                                break;
                            case 'login':

                                    //connection
                            include("Database.php"); 

      
                              $myusername = $_SESSION['nama'];
                              $mypassword = md5($_SESSION['password']); 
                              
                              $sql = "SELECT * FROM anggota WHERE username = '$myusername' and password = '$mypassword'";

                                    if (!$sql) {
                                    die(mysqli_error("errrorr"));
                                }

                              $data = mysqli_query($conn,$sql);
                              // $cek = mysqli_num_rows($data);
 
                                if(mysqli_num_rows($data) > 0){
                                    $_SESSION['nama'] = $myusername;
                                    $_SESSION['status'] = "login";
                                    $hasil="berhasil";
                                }else{
                                    $hasil="gagal";
                                }


                              


                                // session_destroy();
                                break;


                                //end connection
                            
                    }
                        
                        
                    } else {
                        $hasil = "> Hi, Saya tidak tau maksut anda,\n> silahkan klik help untuk bantuan : /help";
                    }
                // }
                break;

        
        case '/help':
             
            $hasil="> untuk melakukan registrasi bot ini silahkan daftar : /daftar.\n> untuk login bot ini silahkan login : /login.\n> untuk menutup bot ini silahkan logout : /logout";
            break;

        case '/logout':
             

            session_start();
            session_destroy();
            $hasil="terimaksih";
            break;


			
    }
	
	print_r($_SESSION);

    return $hasil;
}
 
// jebakan token, klo ga diisi akan mati
if (strlen($TOKEN)<20) 
    die("Token mohon diisi dengan benar!\n");

// fungsi pesan yang sekaligus mengupdate offset 
// biar tidak berulang-ulang pesan yang di dapat 
function process_message($message)
{
    $updateid = $message["update_id"];
    $message_data = $message["message"];
    if (isset($message_data["text"])) {
    $chatid = $message_data["chat"]["id"];
        $message_id = $message_data["message_id"];
        $text = $message_data["text"];
        $response = create_response($text, $message_data);
        if (!empty($response))
          send_reply($chatid, $message_id, $response);
    }
    return $updateid;
}
 
// hapus baris dibawah ini, jika tidak dihapus berarti kamu kurang teliti!
//die("Mohon diteliti ulang codingnya..\nERROR: Hapus baris atau beri komen line ini yak!\n");
 
// hanya untuk metode poll
// fungsi untuk meminta pesan 
function process_one()
{
    global $debug;
    $update_id  = 1;
    echo "-";
 
    if (file_exists("last_update_id")) 
        $update_id = (int)file_get_contents("last_update_id");
 
    $updates = get_updates($update_id);

    // jika debug=0 atau debug=false, pesan ini tidak akan dimunculkan
    if ((!empty($updates)) and ($debug) )  {
        echo "\r\n===== isi diterima \r\n";
        print_r($updates);
    }
 
    foreach ($updates as $message)
    {
        echo '+';
        $update_id = process_message($message);
	}
    
	// @TODO nanti ganti agar update_id disimpan ke database
    // update file id, biar pesan yang diterima tidak berulang
    file_put_contents("last_update_id", $update_id + 1);
}

// metode poll
// proses berulang-ulang
// sampai di break secara paksa
// tekan CTRL+C jika ingin berhenti 
while (true) {
    process_one();
    sleep(1);
}

// metode webhook
// secara normal, hanya bisa digunakan secara bergantian dengan polling
// aktifkan ini jika menggunakan metode webhook
/*
$entityBody = file_get_contents('php://input');
$pesanditerima = json_decode($entityBody, true);
process_message($pesanditerima);
*/
  
?>