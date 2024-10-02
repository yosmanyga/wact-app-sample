<?php
// echo "<ul><li>test1</li><li>test2</li><li>test3</li><li><b>BOLD</b></li></ul>";die;

$ldap_server = "ldap://10.12.1.4";
$auth_user = "yohansipb";
$auth_pass = "9*/8+-qwe0..36+-";

// Set the base dn to search the entire directory.
//$base_dn = "OU=Mecanica,OU=CRD,OU=Pregrado,OU=Estudiantes,OU=FAC_ING_MECANICA,OU=_Usuarios,DC=uclv,DC=edu,DC=cu";
$base_dn = "OU=FAC_ING_MECANICA,OU=_Usuarios,DC=uclv,DC=edu,DC=cu";
//$base_dn = "OU=_Usuarios,DC=uclv,DC=edu,DC=cu";

//$username = current($_POST['username']);
$username = "yohansipb";

// Show only user persons
$filter = "(&(objectClass=user)(objectCategory=person)(cn=".$username."*))";

// Enable to show only users
//$filter = "(&(objectClass=user)(cn=$*))";

// Enable to show everything
// $filter = "(cn=*)";

// connect to server
if (!($connect=@ldap_connect($ldap_server))) {
     die("Could not connect to ldap server");
}

// bind to server
if (!($bind=@ldap_bind($connect, $auth_user, $auth_pass))) {
     die("Unable to bind to server");
}

//if (!($bind=@ldap_bind($connect))) {
//    die("Unable to bind to server");
//}

// search active directory
if (!($search=@ldap_search($connect, $base_dn, $filter))) {
     die("Unable to search ldap server");
}

$number_returned = ldap_count_entries($connect,$search);
$info = ldap_get_entries($connect, $search);

//echo "The number of entries returned is ". $number_returned."<p>";
print_r($info);die;
echo "<ul>";
for ($i=0; $i<$info["count"]; $i++) {
   //echo "<li value='{$info[$i]["samaccountname"][0]}'>{$info[$i]["displayname"][0]}</li>";
   echo "<li>{$info[$i]["samaccountname"][0]}</li>";
   //echo "Name is: ". $info[$i]["name"][0]."<br>";
   //echo "Display name is: ". $info[$i]["displayname"][0]."<br>";
   //echo "Email is: ". $info[$i]["mail"][0]."<br></p>";
}
echo "</ul>";
?> 
