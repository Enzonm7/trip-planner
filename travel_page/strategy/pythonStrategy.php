<?php
//Implement Builder.php and Config.php to create a strategy for calling the python script and getting the results
declare(strict_types=1);

$data=json_encode(["name"=>$city,'long'=>$longitude,"lat"=>$latitude]);
$output=shell_exec("echo '$data' | python3 /home/user/Documents/BOXCERTIFICATIVETRAINING/algo/algo.py");
$groupes=json_decode($output,true);

?>