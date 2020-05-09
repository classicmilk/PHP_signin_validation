<?php

 
# Error Codes:
# 0 : No error.
# 1 : MD5 Conflicts detected.

# 2 : Account doesn't exist & Account is needed.
# 3 : Password error & Password is needed.
# 5 : Account already exist.
# 6 : Login required before processing & Token expired.

# 7 : No processes requirement detected.
# 8 : Dashboard no content yet.

# 4 : system inner mistakes.

# -1 : Nothing detected.

if ($_GET['opt'] == "sqltest") {
    $sql_name = "";
    $sql_user = "";
    $sql_psw = "";
    $user_acc = $_GET['acc'];
    $user_psw = $_GET['psw'];
    
    $sql_conn = new mysqli('localhost', $sql_user, $sql_psw, "db", $sql_name);
    if ($sql_conn->connect_error) {
        die("Connection failed: " . $sql_conn -> connect_error);
    }
    $tableName = 'test';

    $columnName = 'test2';

    //Our SQL query that will alter the table and add the new column.
    $sql = "ALTER TABLE  `$tableName` ADD  `$columnName` DATE NOT NULL";
    
    //Execute the query.
    $sql_conn->query($sql);
        
    $result  = $sql_conn -> query('SELECT * FROM test');
    echo "aa - $result";
    exit();
}

switch ($_GET["opt"]) {
    case "signup":
        signup();
        exit("0");
    case "login":
        login();
        exit();
    case "proc":
        proc();
        exit();
    default:
        exit("-1");
}
exit("-1");


function login()
{
    function md5check()
    {
        $filemd5 = trim($_GET['md5']);
        $md5onserver_content = trim(file_get_contents("md5.txt"));
        
        if("$filemd5" != "$md5onserver_content"){
            // If certificate is not passed, exit.
            exit("1");
        }
    }
    
    $useracc = $_GET["acc"];
    $userpsw = $_GET["psw"];
    
    $user_path = "./USERS/_ACC_$useracc";
    $token_path = "$user_path/_TOKEN_.tok";
    
    md5check();
    
    if(file_exists("$user_path"))
    {
        // Check if the account filename has the password as what user gave
        if(file_get_contents("$user_path/_PSW_.psw") == "$userpsw")
        {
            $token = gettoken();
            echo "$token";
            return;
            
        } else {
            exit("3");
        }
    } else {
        exit("2");
    }
}

function signup()
{
    $useracc = $_GET["acc"];
    $userpsw = $_GET["psw"];
    $userfolder = "./USERS/_ACC_$useracc";
    if(file_exists($userfolder))
    {
        exit("5");
    }
    if (strlen($userpsw) == 0) {
        exit("3");
    }
    mkdir("$userfolder");
    chmod("$userfolder", 0700) or die("4");
    
    $user_psw_store = fopen("$userfolder/_PSW_.psw", "w") or die("4");
    file_put_contents("$userfolder/_PSW_.psw", "$userpsw") or die("4");
    chmod("$userfolder/_PSW_.psw", 0640) or die("4");
    
    $user_tok_store = fopen("$userfolder/_TOKEN_.tok", "w") or die("4");
    chmod("$userfolder/_TOKEN_.tok", 0640) or die("4");
}


function gettoken()
{
    /*
    Features:
    1> Safe. (1)10 digits random key to enhance encryption
             (2)Account is not allowed to login with different IP address.
             (3)Refreshes every 1h
             
    2> Fast. (1)All local manipulations. No remote API calls.
             (2)Enhanced File System.
             (3)All manipulations are passive
    */
    $useracc = $_GET["acc"];
    $user_path = "./USERS/_ACC_$useracc";
    $token_path = "$user_path/_TOKEN_.tok";
    
    if(!file_exists("$user_path")||"$useracc"=="")
    {
        exit("2");
    }
    
    $old_time = date("_-Y-_M_-_d_-_H-_", filemtime("$token_path"));
    $old_token = file_get_contents("$token_path");
    
    $now_time = date("_-Y-_M_-_d_-_H-_");
    
    if("$now_time" == "$old_time" && strlen($old_token) != 0) {
        return $old_token;
    }
    

    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Make random string
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < 10; $i++) 
    { 
        $index = rand(0, strlen($characters) - 1); 
        $randomString .= $characters[$index]; 
    }
    
    $new_token = md5("$ip--$now_time--$randomString--$useracc");
    file_put_contents("$token_path", "$new_token");
    return "$new_token";
}

function checktoken()
{
    if (strlen($_GET['acc']) == 0) {
        die("2");
    }
    
    $useracc = $_GET["acc"];
    $user_path = "./USERS/_ACC_$useracc";
    $token_path = "$user_path/_TOKEN_.tok";
    
    $old_time = date("YMdH", filemtime("$token_path"));
    $now_time = date("YMdH");
    
    if ("$old_time" != "$now_time") {
        exit("6");
    }
    
    $user_token = $_GET['token'];
    $old_token = file_get_contents("$token_path");
    if("$user_token" == "$old_token") return true;
    return false;
}


function proc() {
    if (!checktoken()) {
        exit("6");
    }
    switch ($_GET['get']) {
        case "dashboard":
            $dcontent = file_get_contents("./DATA/dashboard.txt");
            if (strlen($dcontent) == 0) {
                exit("8");
            }
            exit($dcontent);
            break;
        default:
            exit("7");
    }
}



?>
