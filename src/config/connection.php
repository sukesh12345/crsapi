<?php

namespace config;


class dbconnection {

public function connect() {

$fm = new \FileMaker("CampusRecruitment", "http://172.16.9.184", "admin", "12345678");
if (\FileMaker::isError($fm)) {
echo "<p>Error: " . $fm->getMessage() . "</p>";
exit;
}
return $fm;
}
}
?>